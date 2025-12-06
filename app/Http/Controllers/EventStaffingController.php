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

You MUST reply ONLY with a single JSON object in this exact shape:
{ "roles": [ { "name": "Role name", "spots": 0 }, ... ] }

General rules:
- Use ONLY the roles given in the "roles" array from the user.
- "spots" must be non-negative integers (0, 1, 2, 3, ...).
- Consider: venue_area_m2, expected_attendees, and event_category.
- If a role is not needed, set its spots to 0 (do NOT invent new roles).
- Do NOT add any extra keys or any text outside the JSON.
- Treat event_category case-insensitively (e.g. "Wedding", "wedding party" → wedding logic).

EVENT LOGIC (very important):

1) WEDDING (event_category contains "wedding"):
   - Civil Defense MUST ALWAYS be 0.
   - Focus on roles such as:
     * Cooking Team: typically 2–6 people (4–8 if attendees > 400).
     * Decorator: 2–5 people.
     * Media Staff: 2–4 people (photo / video).
     * Cleaner: 2–5 people.
     * Organizer / Coordinator: 1–3 people.
     * Tech Support (if present in roles): 1–2 people.
   - If a role is clearly unrelated to a wedding (e.g. heavy safety / rescue), set its spots to 0.

2) GRADUATION (event_category contains "graduation"):
   - Civil Defense: 0–2 at most, only if clearly needed.
   - Typical roles:
     * Organizer / Ushers / Guides (if present): 4–10 people.
     * Media Staff: 2–4.
     * Tech Support: 2–4.
     * Cleaner: 2–5.

3) RELIGIOUS MASS EVENTS (event_category contains words like "ashura", "religious", "mourning"):
   - These can require bigger safety and cleaning teams.
   - Civil Defense (if present): 8–20 depending on attendees.
   - Crowd / Gate Control roles (if present): 8–20.
   - Cleaner: 8 or more.
   - Cooking Team: 8 or more if food is served.
   - Media Staff: 3–6.

4) CONFERENCE / LECTURE / WORKSHOP (event_category contains "conference", "lecture", "seminar", "workshop"):
   - Civil Defense: MUST be 0 unless the event explicitly involves high risk.
   - Organizer / Registration / Ushers: 2–4.
   - Media Staff: 1–3.
   - Tech Support: 2–4.
   - Cleaner: 1–3.

Scaling rule:
- For small events (<100 attendees), keep numbers on the lower bound.
- For medium events (100–400 attendees), use mid-range values.
- For large events (>400 attendees), use the higher bound or slightly above.
- If a role is not mentioned above but exists in the roles list, assign 0 unless it is obviously useful.

Return ONLY the JSON object with the "roles" array.
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
