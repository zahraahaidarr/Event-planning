<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SeedEventsAndWorkRolesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // -----------------------------
            // 0) Clean old seeded data (safe re-run)
            // -----------------------------
            $seededEventIds = DB::table('events')
                ->where('title', 'like', 'SEED:%')
                ->pluck('event_id')
                ->toArray();

            if (!empty($seededEventIds)) {
                DB::table('work_roles')->whereIn('event_id', $seededEventIds)->delete();
                DB::table('events')->whereIn('event_id', $seededEventIds)->delete();
            }

            // -----------------------------
            // 1) First 10 employees (employee_id) -> created_by
            // -----------------------------
            $employeeIds = DB::table('employees')
                ->orderBy('employee_id')
                ->limit(10)
                ->pluck('employee_id')
                ->toArray();

            if (count($employeeIds) === 0) {
                return;
            }

            // -----------------------------
            // 2) Categories (your IDs from screenshot)
            // -----------------------------
            $categories = [
                ['id' => 2, 'name' => 'wedding'],
                ['id' => 5, 'name' => 'Ashura'],
                ['id' => 6, 'name' => 'Graduation'],
                ['id' => 7, 'name' => 'Birthday'],
                ['id' => 8, 'name' => 'Anniversary'],
                ['id' => 9, 'name' => 'Family Gathering'],
            ];

            // -----------------------------
            // 3) Role types map (from DB to avoid wrong IDs)
            // -----------------------------
            $roleTypeIds = DB::table('role_types')->pluck('role_type_id', 'name')->toArray();

            $ROLE = [
                'Organizer'     => $roleTypeIds['Organizer']     ?? null,
                'Civil Defense' => $roleTypeIds['Civil Defense'] ?? null,
                'Media Staff'   => $roleTypeIds['Media Staff']   ?? null,
                'Tech Support'  => $roleTypeIds['Tech Support']  ?? null,
                'Cleaner'       => $roleTypeIds['Cleaner']       ?? null,
                'Decorator'     => $roleTypeIds['Decorator']     ?? null,
                'Cooking Team'  => $roleTypeIds['Cooking Team']  ?? null,
                'Security'      => $roleTypeIds['Security']      ?? null,
            ];

            // -----------------------------
            // 4) Schedule rules
            // -----------------------------
            $today = Carbon::today();
            $futureBase = Carbon::create(2026, 2, 14, 10, 0, 0);

            $todayTimes = [
                '10:00:00','10:30:00','11:00:00','11:30:00',
                '14:00:00','14:30:00','15:00:00','16:00:00'
            ];

            $cities = ['Beirut','Dbayeh','Jounieh','Byblos','Saida','Tripoli','Zahle','Tyre','Nabatieh','Baalbek'];
            $now = Carbon::now();

            $pick = function(array $arr, int $i) {
                return $arr[$i % count($arr)];
            };

            $eventSpec = function(string $cat, int $i) use ($cities, $pick) {
                $city = $pick($cities, $i);

                switch (strtolower($cat)) {
                    case 'wedding':
                        $venue = ($i % 2 === 0) ? 'Al Saha Restaurant' : 'Wedding Hall';
                        $area = 800 + (($i % 4) * 150);
                        $att  = 180 + (($i % 4) * 40);
                        return [
                            "SEED: Wedding Reception - {$venue}",
                            "A joyful wedding celebration with organized seating, guest flow management, media coverage, and on-site coordination to ensure everything runs smoothly from arrival to closing.",
                            "{$venue}, {$city}",
                            $area,
                            $att
                        ];

                    case 'ashura':
                        $area = 2500 + (($i % 5) * 700);
                        $att  = 700 + (($i % 6) * 250);
                        return [
                            "SEED: Ashura Gathering & Procession Support",
                            "Public gathering requiring structured organization, safety coordination, crowd flow support, media documentation, and continuous cleaning to maintain a safe and respectful environment.",
                            "Main Square, {$city}",
                            $area,
                            $att
                        ];

                    case 'graduation':
                        $area = 900 + (($i % 4) * 200);
                        $att  = 150 + (($i % 6) * 50);
                        return [
                            "SEED: Graduation Ceremony",
                            "A formal graduation ceremony including stage coordination, sound/tech support, guest guidance, documentation, and post-event cleanup to keep the venue organized and welcoming.",
                            "University Hall, {$city}",
                            $area,
                            $att
                        ];

                    case 'birthday':
                        $area = 300 + (($i % 4) * 120);
                        $att  = 40 + (($i % 6) * 20);
                        return [
                            "SEED: Birthday Celebration",
                            "A friendly birthday event with decoration setup, guest management, light food service, photography coverage, and cleaning support to maintain comfort throughout the event.",
                            "Event Space, {$city}",
                            $area,
                            $att
                        ];

                    case 'anniversary':
                        $venue = ($i % 3 === 0) ? 'Al Saha Restaurant' : 'Restaurant Venue';
                        $area = 450 + (($i % 4) * 150);
                        $att  = 60 + (($i % 5) * 25);
                        return [
                            "SEED: Anniversary Dinner - {$venue}",
                            "An elegant anniversary dinner with table arrangement, guest reception, light media coverage, and smooth coordination to ensure a calm and high-quality experience.",
                            "{$venue}, {$city}",
                            $area,
                            $att
                        ];

                    case 'family gathering':
                    default:
                        $area = 500 + (($i % 5) * 160);
                        $att  = 60 + (($i % 6) * 30);
                        return [
                            "SEED: Family Gathering",
                            "A warm family gathering with organized seating, decoration support, food service coordination, and cleaning to keep the venue comfortable and well-managed.",
                            "Community Hall, {$city}",
                            $area,
                            $att
                        ];
                }
            };

            $rolePlan = function(string $cat, int $attendees, float $area) {
                $catLower = strtolower($cat);

                $organizer = max(1, (int)ceil($attendees / 200));
                $media     = max(1, (int)ceil($attendees / 180));
                $cleaner   = max(1, (int)ceil($attendees / 60));

                $decorator = max(1, (int)ceil($attendees / 90));
                $cooking   = max(1, (int)ceil($attendees / 80));

                $tech = 0; $security = 0; $civil = 0;

                if ($catLower === 'ashura') {
                    $organizer = max($organizer, 3);
                    $media     = max($media, 2);
                    $cleaner   = max($cleaner, 6);

                    $decorator = max(2, (int)ceil($attendees / 250));
                    $cooking   = max(3, (int)ceil($attendees / 220));

                    $tech     = max(1, (int)ceil($area / 2500));
                    $security = max(4, (int)ceil($attendees / 160));
                    $civil    = max(2, (int)ceil($attendees / 500));
                } else {
                    if (in_array($catLower, ['wedding','graduation'], true)) {
                        $tech = ($attendees >= 250 || $area >= 1200) ? 2 : 1;
                    }

                    if ($attendees >= 250) {
                        $security = max(2, (int)ceil($attendees / 220));
                    }
                }

                if (in_array($catLower, ['birthday','anniversary'], true)) {
                    $tech = 0;
                    $security = ($attendees >= 120) ? 1 : 0;
                    $civil = 0;
                }

                if ($catLower === 'family gathering') {
                    $security = ($attendees >= 180) ? 1 : 0;
                    $tech = ($area >= 900) ? 1 : 0;
                }

                $roles = [
                    'Organizer'    => $organizer,
                    'Media Staff'  => $media,
                    'Cleaner'      => $cleaner,
                    'Decorator'    => $decorator,
                    'Cooking Team' => $cooking,
                ];

                if ($tech > 0)     $roles['Tech Support']  = $tech;
                if ($security > 0) $roles['Security']      = $security;
                if ($civil > 0)    $roles['Civil Defense'] = $civil;

                return $roles;
            };

            // -----------------------------
            // 5) Create 3 events per employee (10 employees => 30 events)
            // 8 events today, the rest after 2026-02-13
            // -----------------------------
            $eventIndex = 0;

            foreach ($employeeIds as $empId) {
                for ($k = 0; $k < 3; $k++) {

                    $cat = $categories[$eventIndex % count($categories)];
                    [$title, $desc, $location, $area, $att] = $eventSpec($cat['name'], $eventIndex);

                    $duration = ($eventIndex % 2 === 0) ? 2.00 : 3.00;

                    if ($eventIndex < 8) {
                        $start = $today->copy()->setTimeFromTimeString($todayTimes[$eventIndex]);
                    } else {
                        $start = $futureBase->copy()
                            ->addDays($eventIndex - 8)
                            ->setTime(14, 0, 0);
                    }

                    $end = $start->copy()->addHours((int)$duration);

                    $roles = $rolePlan($cat['name'], $att, (float)$area);
                    $totalSpots = array_sum($roles);

                    $eventId = DB::table('events')->insertGetId([
                        'title' => $title,
                        'description' => $desc,
                        'image_path' => null,           // ✅ your preference
                        'category_id' => $cat['id'],
                        'location' => $location,
                        'starts_at' => $start->format('Y-m-d H:i:s'),
                        'ends_at' => $end->format('Y-m-d H:i:s'),
                        'duration_hours' => $duration,
                        'venue_area_sqm' => (float)$area,
                        'expected_attendance' => (int)$att,
                        'total_spots' => (int)$totalSpots,
                        'status' => 'PUBLISHED',
                        'staffing_mode' => 'MANUAL',
                        'created_by' => (int)$empId,    // ✅ employees.employee_id
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], 'event_id');

                    foreach ($roles as $roleName => $spots) {
                        $roleTypeId = $ROLE[$roleName] ?? null;
                        if (!$roleTypeId) continue;

                        DB::table('work_roles')->insert([
                            'event_id' => $eventId,
                            'role_type_id' => $roleTypeId,
                            'role_name' => $roleName,
                            'required_spots' => (int)$spots,
                            'calc_source' => 'MANUAL',
                            'calc_confidence' => null,
                            'description' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    $eventIndex++;
                }
            }
        });
    }
}
