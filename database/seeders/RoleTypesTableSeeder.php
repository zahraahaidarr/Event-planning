<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('role_types')->insert([
            [
                'name'        => 'Organizer',
                'description' => 'Coordinates event planning and volunteer organization.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Civil Defense',
                'description' => 'Handles emergency response and safety procedures.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Media Staff',
                'description' => 'Manages photography, videography, and event media coverage.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Tech Support',
                'description' => 'Responsible for sound systems, lighting, and technical setup.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Cleaner',
                'description' => 'Keeps the event and surrounding areas clean and organized.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Decorator',
                'description' => 'Designs and arranges event decorations.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'name'        => 'Cooking Team',
                'description' => 'Prepares and serves food for volunteers and attendees.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);
    }
}
