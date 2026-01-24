<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TodayReservationsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // -----------------------------
            // 1) Read enum values for status (to match exact case)
            // -----------------------------
            $col = DB::select("SHOW COLUMNS FROM workers_reservations LIKE 'status'");
            $type = $col[0]->Type ?? '';
            $allowed = [];

            if (preg_match("/^enum\((.*)\)$/i", $type, $m)) {
                preg_match_all("/'([^']+)'/", $m[1], $mm);
                $allowed = $mm[1] ?? [];
            }

            $statusValue = function (string $want) use ($allowed) {
                if (empty($allowed)) return strtoupper($want);
                foreach ($allowed as $v) {
                    if (strtolower($v) === strtolower($want)) return $v;
                }
                return $allowed[0];
            };

            $STATUS_RESERVED = $statusValue('RESERVED');
            $STATUS_PENDING  = $statusValue('PENDING');
            $STATUS_REJECTED = $statusValue('REJECTED');

            // statuses that "occupy" a spot (accepted)
            $occupyCandidates = ['RESERVED','CHECKED_IN','CHECKED_OUT','COMPLETED','NO_SHOW'];
            $OCCUPY = [];
            foreach ($occupyCandidates as $c) {
                $val = $statusValue($c);
                if (!in_array($val, $OCCUPY, true)) $OCCUPY[] = $val;
            }

            // -----------------------------
            // 2) Today events only
            // -----------------------------
            $today = Carbon::today()->toDateString();

            $todayEventIds = DB::table('events')
                ->whereDate('starts_at', $today)
                ->pluck('event_id')
                ->toArray();

            if (empty($todayEventIds)) return;

            // -----------------------------
            // 3) ACTIVE workers only (join users)
            // -----------------------------
            $activeWorkers = DB::table('workers')
                ->join('users', 'users.id', '=', 'workers.user_id')
                ->where('users.role', 'WORKER')
                ->where('users.status', 'ACTIVE')
                ->select('workers.worker_id', 'workers.role_type_id')
                ->orderBy('workers.worker_id')
                ->get();

            if ($activeWorkers->isEmpty()) return;

            $workersByRoleType = [];
            $allActiveWorkerIds = [];

            foreach ($activeWorkers as $w) {
                $workersByRoleType[$w->role_type_id][] = (int)$w->worker_id;
                $allActiveWorkerIds[] = (int)$w->worker_id;
            }

            $now = Carbon::now();

            // -----------------------------
            // 4) Seed reservations per event
            // -----------------------------
            foreach ($todayEventIds as $eventId) {

                // roles for this event
                $roles = DB::table('work_roles')
                    ->where('event_id', $eventId)
                    ->select('role_id', 'role_type_id', 'required_spots')
                    ->orderByDesc('required_spots')
                    ->get();

                if ($roles->isEmpty()) continue;

                // workers already having any reservation in this event (avoid duplicates)
                $existingWorkerIds = DB::table('workers_reservations')
                    ->where('event_id', $eventId)
                    ->pluck('worker_id')
                    ->map(fn($x) => (int)$x)
                    ->toArray();

                $used = array_fill_keys($existingWorkerIds, true);

                // -----------------------------
                // 4.A) Fill RESERVED per role up to required_spots (based on available active workers)
                // -----------------------------
                foreach ($roles as $role) {

                    // how many spots already occupied (accepted)
                    $occupied = DB::table('workers_reservations')
                        ->where('event_id', $eventId)
                        ->where('work_role_id', $role->role_id)
                        ->whereIn('status', $OCCUPY)
                        ->count();

                    $need = (int)$role->required_spots - (int)$occupied;
                    if ($need <= 0) continue;

                    $candidates = $workersByRoleType[$role->role_type_id] ?? [];
                    if (empty($candidates)) continue;

                    // deterministic order
                    sort($candidates);

                    foreach ($candidates as $workerId) {
                        if ($need <= 0) break;
                        if (isset($used[$workerId])) continue;

                        DB::table('workers_reservations')->updateOrInsert(
                            [
                                'event_id' => $eventId,
                                'work_role_id' => $role->role_id,
                                'worker_id' => $workerId,
                            ],
                            [
                                'reserved_at' => $now,
                                'status' => $STATUS_RESERVED,
                                'check_in_time' => null,
                                'check_out_time' => null,
                                'credited_hours' => null,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]
                        );

                        $used[$workerId] = true;
                        $need--;
                    }
                }

                // -----------------------------
                // 4.B) Ensure exactly 2 PENDING + 2 REJECTED per event (top-up)
                // -----------------------------
                $pendingCount = DB::table('workers_reservations')
                    ->where('event_id', $eventId)
                    ->where('status', $STATUS_PENDING)
                    ->count();

                $rejectedCount = DB::table('workers_reservations')
                    ->where('event_id', $eventId)
                    ->where('status', $STATUS_REJECTED)
                    ->count();

                $pendingNeed  = max(0, 2 - (int)$pendingCount);
                $rejectedNeed = max(0, 2 - (int)$rejectedCount);

                if ($pendingNeed === 0 && $rejectedNeed === 0) continue;

                // attach pending/rejected to the "main" role (largest required_spots)
                $mainRole = $roles->first();
                $mainRoleId = (int)$mainRole->role_id;
                $mainRoleType = (int)$mainRole->role_type_id;

                // prefer same role_type candidates first
                $extraCandidates = array_values(array_filter(
                    $workersByRoleType[$mainRoleType] ?? [],
                    fn($wid) => !isset($used[(int)$wid])
                ));

                sort($extraCandidates);

                // if not enough, take any active workers not used
                if (count($extraCandidates) < ($pendingNeed + $rejectedNeed)) {
                    $fallback = array_values(array_filter(
                        $allActiveWorkerIds,
                        fn($wid) => !isset($used[(int)$wid])
                    ));
                    sort($fallback);
                    $extraCandidates = array_values(array_unique(array_merge($extraCandidates, $fallback)));
                }

                $needTotal = $pendingNeed + $rejectedNeed;
                $picked = array_slice($extraCandidates, 0, $needTotal);

                // insert pending first, then rejected
                $pendingWorkers = array_slice($picked, 0, $pendingNeed);
                $rejectedWorkers = array_slice($picked, $pendingNeed, $rejectedNeed);

                foreach ($pendingWorkers as $workerId) {
                    $workerId = (int)$workerId;

                    DB::table('workers_reservations')->updateOrInsert(
                        [
                            'event_id' => $eventId,
                            'work_role_id' => $mainRoleId,
                            'worker_id' => $workerId,
                        ],
                        [
                            'reserved_at' => $now,
                            'status' => $STATUS_PENDING,
                            'check_in_time' => null,
                            'check_out_time' => null,
                            'credited_hours' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );

                    $used[$workerId] = true;
                }

                foreach ($rejectedWorkers as $workerId) {
                    $workerId = (int)$workerId;

                    DB::table('workers_reservations')->updateOrInsert(
                        [
                            'event_id' => $eventId,
                            'work_role_id' => $mainRoleId,
                            'worker_id' => $workerId,
                        ],
                        [
                            'reserved_at' => $now,
                            'status' => $STATUS_REJECTED,
                            'check_in_time' => null,
                            'check_out_time' => null,
                            'credited_hours' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );

                    $used[$workerId] = true;
                }
            }
        });
    }
}
