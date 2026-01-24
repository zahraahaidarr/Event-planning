<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VenueSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('venues')->insert([
            // ✅ Good for wedding / anniversary
            ['name' => 'Al Saha Restaurant', 'city' => 'Beirut', 'area_m2' => 900, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Le Royal Hotel & Resort Ballroom', 'city' => 'Dbayeh', 'area_m2' => 2200, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Phoenicia Hotel Ballroom', 'city' => 'Beirut', 'area_m2' => 1800, 'created_at' => $now, 'updated_at' => $now],

            // ✅ Good for graduation
            ['name' => 'AUB Assembly Hall', 'city' => 'Beirut', 'area_m2' => 1400, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'LAU Auditorium', 'city' => 'Beirut', 'area_m2' => 1100, 'created_at' => $now, 'updated_at' => $now],

            // ✅ Good for family gathering / birthday
            ['name' => 'Beit Beirut Event Hall', 'city' => 'Beirut', 'area_m2' => 650, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Byblos Old Souk Courtyard', 'city' => 'Byblos', 'area_m2' => 1200, 'created_at' => $now, 'updated_at' => $now],

            // ✅ Good for Ashura / large gatherings
            ['name' => 'Nabatieh Main Square', 'city' => 'Nabatieh', 'area_m2' => 9000, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Tyre Seaside Promenade Area', 'city' => 'Tyre', 'area_m2' => 7000, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Camille Chamoun Sports City', 'city' => 'Beirut', 'area_m2' => 35000, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
