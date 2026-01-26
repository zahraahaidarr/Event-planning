<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PostEventSubmissionsSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ 20 reservation IDs FROM YOUR SCREENSHOT (workers_reservations)
        $reservationIds = [
            66,67,68,69,70,71,72,73,74,75,76,
            78,79,80,
            81,82,83,84,85,86
        ];

        // 1) Ensure these reservations are COMPLETED (so we can create approved reports)
        //    (We keep real IDs; we only update status/times.)
        foreach ($reservationIds as $rid) {
            $wr = DB::table('workers_reservations')
                ->where('reservation_id', $rid)
                ->first();

            if (!$wr) {
                throw new \RuntimeException("Reservation ID {$rid} not found in workers_reservations.");
            }

            // if already has check times keep them, else set simple realistic ones
            $checkIn  = $wr->check_in_time  ?: Carbon::parse($wr->reserved_at)->addHours(8)->format('Y-m-d H:i:s');
            $checkOut = $wr->check_out_time ?: Carbon::parse($checkIn)->addHours(3)->format('Y-m-d H:i:s');

            DB::table('workers_reservations')
                ->where('reservation_id', $rid)
                ->update([
                    'status'        => 'COMPLETED',
                    'check_in_time' => $checkIn,
                    'check_out_time'=> $checkOut,
                    'credited_hours'=> $wr->credited_hours ?: 3.00,
                    'updated_at'    => now(),
                ]);
        }

        // 2) Remove old reports for these reservations (avoid duplicates if you rerun)
        DB::table('post_event_submissions')
            ->whereIn('worker_reservation_id', $reservationIds)
            ->delete();

        // 3) Pull the UPDATED reservations
        $reservations = DB::table('workers_reservations')
            ->whereIn('reservation_id', $reservationIds)
            ->orderBy('reservation_id')
            ->get([
                'reservation_id','event_id','worker_id','work_role_id',
                'reserved_at','check_in_time','check_out_time','credited_hours'
            ]);

        // 4) role_name + slug comes from work_roles (your screenshot shows this table)
        $roleMap = DB::table('work_roles')
            ->whereIn('role_id', $reservations->pluck('work_role_id'))
            ->pluck('role_name', 'role_id'); // [role_id => role_name]

        // 5) Ratings (mixed, not the same, always filled)
        $ownerRatings  = [5,4,3,2,4,5,3,2,5,4,3,2,4,5,3,2,5,4,3,2];
        $workerRatings = [4,5,2,3,5,3,4,2,3,5,2,4,5,3,2,4,3,5,2,4];

        $rows = [];
        $i = 0;

        foreach ($reservations as $r) {
            $roleName = $roleMap[$r->work_role_id] ?? 'Role';
            $roleSlug = $this->toSlug($roleName);

            $submittedAt = Carbon::parse($r->check_out_time)->addMinutes(20);
            $reviewedAt  = (clone $submittedAt)->addHours(2);

            $rows[] = [
                'worker_reservation_id' => $r->reservation_id,
                'event_id'              => $r->event_id,
                'worker_id'             => $r->worker_id,
                'work_role_id'          => $r->work_role_id,
                'role_slug'             => $roleSlug,

                'general_notes'         => null,
                'data'                  => json_encode($this->buildDataPayload($roleSlug), JSON_UNESCAPED_UNICODE),

                'status'                => 'approved',

                // ✅ always filled + mixed
                'owner_rating'          => $ownerRatings[$i],
                'worker_rating'         => $workerRatings[$i],

                // ✅ match your existing data pattern
                'submitted_at'          => $submittedAt->format('Y-m-d H:i:s'),
                'reviewed_at'           => $reviewedAt->format('Y-m-d H:i:s'),
                'reviewed_by'           => 59,
                'review_notes'          => null,

                'created_at'            => $submittedAt->format('Y-m-d H:i:s'),
                'updated_at'            => $reviewedAt->format('Y-m-d H:i:s'),
            ];

            $i++;
        }

        DB::table('post_event_submissions')->insert($rows);

        $this->command?->info("Inserted " . count($rows) . " post_event_submissions rows (approved, completed reservations).");
    }

    private function toSlug(string $name): string
    {
        $name = trim(mb_strtolower($name));
        $name = preg_replace('/[^a-z0-9]+/i', '_', $name);
        return trim($name, '_') ?: 'role';
    }

    private function buildDataPayload(string $roleSlug): array
    {
        // simple realistic payloads per role
        switch ($roleSlug) {
            case 'cooking_team':
                return [
                    'meals' => "Rice plates: 180\nChicken sandwiches: 90\nWater bottles: 200",
                    'notes' => "Served on time and maintained hygiene."
                ];
            case 'civil_defense':
                return [
                    'cases' => "Minor incident handled.\nFirst-aid readiness confirmed.",
                    'notes' => "Safety checks completed."
                ];
            case 'media_staff':
                return [
                    'deliverables' => "Photos: 120\nShort clips: 6\nHighlights reel: 1",
                    'notes'        => "Captured key moments."
                ];
            case 'tech_support':
                return [
                    'setup' => "Audio tested, mic levels adjusted, lighting checked.",
                    'notes' => "Resolved minor equipment issue."
                ];
            case 'cleaner':
                return [
                    'areas' => "Hall + entrance + restrooms",
                    'notes' => "Cleanup completed before event end."
                ];
            case 'decorator':
                return [
                    'setup' => "Stage decor + tables + entrance banner",
                    'notes' => "Decoration matched theme."
                ];
            case 'security':
                return [
                    'incidents' => "No major incidents.\nCrowd flow managed at entrance.",
                    'notes'     => "Maintained order."
                ];
            case 'organizer':
                return [
                    'coordination' => "Assigned tasks and managed timeline.",
                    'notes'        => "Operations ran smoothly."
                ];
            default:
                return [
                    'summary' => "Tasks completed successfully.",
                    'notes'   => "No issues reported."
                ];
        }
    }
}
