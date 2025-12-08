<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EventStaffingController extends Controller
{
    public function predictRoles(Request $request)
    {
        // 1) Validate JSON coming from your JS
        $data = $request->validate([
            'venue_area_m2'      => 'required|numeric|min:1',
            'expected_attendees' => 'required|numeric|min:1',
            'category'           => 'required|string',
            'available_roles'    => 'required|array|min:1',
            'available_roles.*'  => 'string',
        ]);

        $apiKey = config('services.openai.key');

        if (! $apiKey) {
            return response()->json([
                'ok'    => false,
                'error' => 'OpenAI API key is missing (check .env / config).',
            ], 500);
        }

        // 2) Prepare event payload to send to the model
        $payloadForModel = [
            'venue_area_m2'      => $data['venue_area_m2'],
            'expected_attendees' => $data['expected_attendees'],
            'event_category'     => $data['category'],
            'roles'              => $data['available_roles'],
        ];

        try {
            // 3) Call OpenAI Chat Completions in JSON mode
            $response = Http::withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'           => 'gpt-4o-mini',
                    'temperature'     => 0.2,
                    'response_format' => ['type' => 'json_object'],
                    'messages'        => [
                        [
                            'role'    => 'system',
'content' => <<<SYS
You are an assistant that plans volunteer staffing for events.

You MUST reply ONLY with a JSON object in this exact structure:
{ "roles": [ { "name": "Role name", "spots": 0 }, ... ] }

General Rules:
- Use ONLY the roles listed in the “roles” array from the user.
- Assign a non-negative integer to each role.
- If a role is clearly unnecessary for the specific event type, assign 0.
- All logic MUST be consistent, explainable, and scale with expected_attendees.
- Do NOT add any text outside the JSON.

HOW TO THINK (very important):
1. Determine the event category (wedding, graduation, ashura/religious, conference, etc.).
2. Identify which roles are typically required for this category.
3. For each required role, calculate a reasonable staffing number based on:
   • expected_attendees  
   • type of activity  
   • level of coordination or safety needed  
   • complexity of logistics  
4. The number of workers for each role MUST increase logically when attendees increase.
5. If a role is listed but is irrelevant for the event category, set it to 0.

SCALING LOGIC (general, not strict):
- Small events (<100 attendees): small teams  
- Medium events (100–400 attendees): moderate teams  
- Large events (>400 attendees): large teams  
Scale each role proportionally based on its responsibility level.

CATEGORY GUIDELINES (not numeric, just logical):

WEDDING:
- Normally needs: Cooking Team, Decorators, Cleaners, Media Staff, Organizers.
- Does NOT normally need Civil Defense or heavy rescue roles → set to 0.

GRADUATION:
- Needs Organizers, Ushers, Media Staff, Tech Support, Cleaners,security.
- Only minimal safety roles if absolutely necessary.

RELIGIOUS MASS EVENTS / ASHURA:
- High coordination events with many moving parts.
- Needs: Organizers:1-2, Crowd Control, Cleaners, possibly Cooking Team, Media Staff.
- security/Civil Defense roles SHOULD exist and scale with crowd size.
- Safety roles must increase logically with attendees, not randomly.

CONFERENCE / WORKSHOP:
- Needs: Organizer, Tech Support, Cleaner, Media Staff.
- Safety roles typically 0 unless the event implies risk.

ROLE ASSIGNMENT PRINCIPLES:
- Start from the expected_attendees value.
- Assign staff proportionally:
  • Organizational roles → scale slowly  
  • Cleaning / serving roles → scale moderately  
  • Crowd control / safety roles → scale only if the event type requires it  
- Maintain internal consistency:
  - If attendees double, staffing should increase but not explode.
  - Avoid extreme or unrealistic numbers.

Return ONLY the JSON with the roles and computed spots.

SYS,



                        ],
                        [
                            'role'    => 'user',
                            'content' => json_encode($payloadForModel),
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('OpenAI staffing error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return response()->json([
                    'ok'    => false,
                    'error' => 'Failed to contact AI model.',
                ], 500);
            }

            // 4) Parse model JSON
            $content = $response->json('choices.0.message.content');
            $decoded = json_decode($content, true);

            if (! is_array($decoded) || ! isset($decoded['roles']) || ! is_array($decoded['roles'])) {
                Log::warning('Unexpected AI response structure', ['content' => $content]);

                return response()->json([
                    'ok'    => false,
                    'error' => 'AI returned unexpected format.',
                ], 500);
            }

            $allowedRoles = $data['available_roles'];

            // Build a map from role name -> spots from AI result
            $aiRoleMap = [];
            foreach ($decoded['roles'] as $roleObj) {
                $name  = $roleObj['name']  ?? null;
                $spots = $roleObj['spots'] ?? 0;

                if (! $name || ! in_array($name, $allowedRoles, true)) {
                    continue; // skip unknown roles
                }

                $aiRoleMap[$name] = max(0, (int) $spots);
            }

            // 5) Normalize into the exact shape JS expects:
            // "role_capacities": { "RoleName" : integer, ... }
            $roleCaps = [];
            foreach ($allowedRoles as $roleName) {
                $roleCaps[$roleName] = $aiRoleMap[$roleName] ?? 0;
            }

            Log::info('AI staffing result (normalized)', ['role_capacities' => $roleCaps]);

            return response()->json([
                'ok'              => true,
                'role_capacities' => $roleCaps,
            ]);
        } catch (\Throwable $e) {
            Log::error('Exception in predictRoles', ['exception' => $e]);

            return response()->json([
                'ok'    => false,
                'error' => 'Server error while predicting roles.',
            ], 500);
        }
    }
}
