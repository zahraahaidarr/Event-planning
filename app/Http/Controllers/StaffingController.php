<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StaffingController extends Controller
{
    /**
     * POST /api/ai/staffing
     * Expects JSON:
     * { venue_area_m2, expected_attendees, category, available_roles: [..] }
     * Returns:
     * { roles: [{name, spots}], total_spots }
     */
    public function predict(Request $request)
    {
        $data = $request->validate([
            'venue_area_m2'       => ['nullable','numeric','min:0'],
            'expected_attendees'  => ['nullable','integer','min:0'],
            'category'            => ['nullable','string','max:100'],
            'available_roles'     => ['nullable','array'],
            'available_roles.*'   => ['string','max:100'],
        ]);

        $venue  = (float)($data['venue_area_m2'] ?? 0);
        $people = (int)($data['expected_attendees'] ?? 0);
        $cat    = trim($data['category'] ?? 'community');
        $roles  = $data['available_roles'] ?? ['Organizer','Civil Defense','Media Staff','Tech Support','Cleaner','Decorator','Cooking Team','Waiter'];

        $openaiKey = config('services.openai.key');

        // 1) Try OpenAI if key exists
        if (!empty($openaiKey)) {
            try {
                // Prompt the model to return **strict JSON**
                $system = "You are a staffing planner for volunteer events. Respond ONLY with minified JSON that matches this PHP schema:
{ \"roles\": [{\"name\": string, \"spots\": integer}], \"total_spots\": integer }.
Do not add explanations or extra fields.";

                $user = [
                    'venue_area_m2'      => $venue,
                    'expected_attendees' => $people,
                    'category'           => $cat,
                    'available_roles'    => array_values($roles),
                    // nudge: constraints
                    'rules' => [
                        'spots must be non-negative integers',
                        'total_spots should equal sum of role spots',
                        'prefer Organizer and Media Staff as base layers',
                        'scale by attendees and venue; larger events need more Tech Support and Civil Defense',
                    ],
                ];

                $resp = Http::withToken($openaiKey)
                    ->timeout(20)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-4o-mini',   // use any chat model you have access to
                        'response_format' => ['type' => 'json_object'],
                        'messages' => [
                            ['role' => 'system', 'content' => $system],
                            ['role' => 'user',   'content' => json_encode($user, JSON_UNESCAPED_UNICODE)],
                        ],
                        'temperature' => 0.2,
                    ]);

                if ($resp->failed()) {
                    Log::warning('AI staffing http error', ['status' => $resp->status(), 'body' => $resp->body()]);
                    // fall through to heuristic
                } else {
                    $json = $resp->json();

                    // OpenAI format: choices[0].message.content (string JSON)
                    $content = data_get($json, 'choices.0.message.content');
                    if (is_string($content)) {
                        $ai = json_decode($content, true);
                        if (is_array($ai) && isset($ai['roles']) && isset($ai['total_spots'])) {
                            // Basic sanity
                            $ai['roles'] = array_values(array_map(function ($r) {
                                return [
                                    'name'  => (string)($r['name'] ?? ''),
                                    'spots' => max(0, (int)($r['spots'] ?? 0)),
                                ];
                            }, $ai['roles']));

                            $sum = collect($ai['roles'])->sum('spots');
                            $ai['total_spots'] = (int)$sum;

                            return response()->json($ai);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error('AI staffing exception: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
                // fall back to heuristic
            }
        }

        // 2) Fallback heuristic (never fails)
        $result = $this->heuristicPlan($venue, $people, $cat, $roles);
        return response()->json($result);
    }

    /**
     * A simple deterministic rule-based planner so the UI always works even without OpenAI.
     */
    protected function heuristicPlan(float $venue, int $people, string $cat, array $roles): array
    {
        // base per 100 attendees
        $per100 = [
            'Organizer'     => 1.0,
            'Media Staff'   => 0.3,
            'Tech Support'  => 0.3,
            'Civil Defense' => 0.4,
            'Cleaner'       => 0.4,
            'Decorator'     => 0.2,
            'Cooking Team'  => 0.0,
            'Waiter'        => 0.0,
        ];

        // Category adjustments
        $cat = Str::lower($cat);
        if (Str::contains($cat, 'education')) {
            $per100['Organizer']     += 0.3;
            $per100['Media Staff']   += 0.1;
        } elseif (Str::contains($cat, 'environment')) {
            $per100['Cleaner']       += 0.6;
            $per100['Organizer']     += 0.2;
        } elseif (Str::contains($cat, 'health')) {
            $per100['Civil Defense'] += 0.6;
            $per100['Organizer']     += 0.2;
        } elseif (Str::contains($cat, 'community') || Str::contains($cat, 'elderly')) {
            $per100['Organizer']     += 0.2;
            $per100['Waiter']        += 0.2;
            $per100['Cooking Team']  += 0.3;
        }

        // Venue density tweak (bigger venue → slightly more organizers/tech)
        if ($venue > 800) {
            $per100['Organizer']    += 0.2;
            $per100['Tech Support'] += 0.2;
        }

        $scale = max(1, (int)ceil($people / 100));
        $picked = [];
        foreach ($roles as $r) {
            $base = $per100[$r] ?? 0.1; // unknown roles get tiny base
            $spots = (int)round($base * $scale);
            if ($spots > 0) {
                $picked[] = ['name' => $r, 'spots' => $spots];
            }
        }

        // Ensure at least 1 organizer
        $hasOrg = collect($picked)->firstWhere('name', 'Organizer');
        if (!$hasOrg) $picked[] = ['name' => 'Organizer', 'spots' => max(1, (int)round(0.8 * $scale))];

        $total = collect($picked)->sum('spots');

        return ['roles' => array_values($picked), 'total_spots' => (int)$total];
    }
}
