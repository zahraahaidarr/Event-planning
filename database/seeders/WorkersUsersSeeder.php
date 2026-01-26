<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class WorkersUsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // ✅ 10 NEW users (role WORKER, status ACTIVE)
            // emails must be unique in your DB.
            $users = [
                ['first_name'=>'Rana',   'last_name'=>'Haddad',  'email'=>'worker101@gmail.com', 'phone'=>'70101010', 'dob'=>'2002-01-10'],
                ['first_name'=>'Mira',   'last_name'=>'Khalil',  'email'=>'worker102@gmail.com', 'phone'=>'70101011', 'dob'=>'2001-03-22'],
                ['first_name'=>'Nada',   'last_name'=>'Hussein', 'email'=>'worker103@gmail.com', 'phone'=>'70101012', 'dob'=>'2000-07-19'],
                ['first_name'=>'Hala',   'last_name'=>'Yassin',  'email'=>'worker104@gmail.com', 'phone'=>'70101013', 'dob'=>'2003-05-12'],
                ['first_name'=>'Samer',  'last_name'=>'Mansour', 'email'=>'worker105@gmail.com', 'phone'=>'70101014', 'dob'=>'1999-10-21'],
                ['first_name'=>'Omar',   'last_name'=>'Fadel',   'email'=>'worker106@gmail.com', 'phone'=>'70101015', 'dob'=>'2002-02-14'],
                ['first_name'=>'Lina',   'last_name'=>'Rahhal',  'email'=>'worker107@gmail.com', 'phone'=>'70101016', 'dob'=>'2004-06-18'],
                ['first_name'=>'Kareem', 'last_name'=>'Hassan',  'email'=>'worker108@gmail.com', 'phone'=>'70101017', 'dob'=>'1998-07-23'],
                ['first_name'=>'Yara',   'last_name'=>'Saad',    'email'=>'worker109@gmail.com', 'phone'=>'70101018', 'dob'=>'2001-11-26'],
                ['first_name'=>'Rami',   'last_name'=>'Nasser',  'email'=>'worker110@gmail.com', 'phone'=>'70101019', 'dob'=>'2000-02-16'],
            ];

            // role_type_id must exist in role_types table ✅ (1,2,3,4,5,6,7,11)
            // We'll mix VOLUNTEER/PAID similar to your current data.
            $workersProfile = [
                ['role_type_id'=>2,  'engagement_kind'=>'VOLUNTEER', 'is_volunteer'=>1, 'location'=>'beirut',   'hourly_rate'=>null],
                ['role_type_id'=>5,  'engagement_kind'=>'PAID',      'is_volunteer'=>0, 'location'=>'byblos',   'hourly_rate'=>10.00],
                ['role_type_id'=>7,  'engagement_kind'=>'PAID',      'is_volunteer'=>0, 'location'=>'beirut',   'hourly_rate'=>12.00],
                ['role_type_id'=>6,  'engagement_kind'=>'VOLUNTEER', 'is_volunteer'=>1, 'location'=>'beirut',   'hourly_rate'=>null],
                ['role_type_id'=>3,  'engagement_kind'=>'VOLUNTEER', 'is_volunteer'=>1, 'location'=>'beirut',   'hourly_rate'=>null],
                ['role_type_id'=>1,  'engagement_kind'=>'VOLUNTEER', 'is_volunteer'=>1, 'location'=>'beirut',   'hourly_rate'=>null],
                ['role_type_id'=>11, 'engagement_kind'=>'PAID',      'is_volunteer'=>0, 'location'=>'nabatieh', 'hourly_rate'=>15.00],
                ['role_type_id'=>4,  'engagement_kind'=>'PAID',      'is_volunteer'=>0, 'location'=>'beirut',   'hourly_rate'=>14.00],
                ['role_type_id'=>2,  'engagement_kind'=>'VOLUNTEER', 'is_volunteer'=>1, 'location'=>'tyre',     'hourly_rate'=>null],
                ['role_type_id'=>5,  'engagement_kind'=>'VOLUNTEER', 'is_volunteer'=>1, 'location'=>'tripoli',  'hourly_rate'=>null],
            ];

            // safety check: role_types exist
            $roleTypeIds = array_values(array_unique(array_column($workersProfile, 'role_type_id')));
            $existsCount = DB::table('role_types')->whereIn('role_type_id', $roleTypeIds)->count();
            if ($existsCount !== count($roleTypeIds)) {
                throw new \RuntimeException("Some role_type_id values do not exist in role_types table.");
            }

            foreach ($users as $idx => $u) {
                // avoid duplicates if rerun
                $existingUser = DB::table('users')->where('email', $u['email'])->first();
                if ($existingUser) {
                    continue;
                }

                $now = now();

                $userId = DB::table('users')->insertGetId([
                    'first_name'        => $u['first_name'],
                    'last_name'         => $u['last_name'],
                    'email'             => $u['email'],
                    'phone'             => $u['phone'],
                    'date_of_birth'     => $u['dob'],
                    'avatar_path'       => null,
                    'role'              => 'WORKER',
                    'status'            => 'ACTIVE',
                    'email_verified_at' => null,
                    'password'          => Hash::make('Password@123'), // you can change
                    'remember_token'    => null,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

                $wp = $workersProfile[$idx];

                DB::table('workers')->insert([
                    'user_id'             => $userId,
                    'role_type_id'        => $wp['role_type_id'],
                    'engagement_kind'     => $wp['engagement_kind'],   // VOLUNTEER / PAID
                    'is_volunteer'        => $wp['is_volunteer'],      // 1/0
                    'location'            => $wp['location'],
                    'certificate_path'    => null,

                    'total_hours'         => 0,
                    'verification_status' => 'PENDING',
                    'hourly_rate'         => $wp['hourly_rate'],
                    'approved_by'         => null,
                    'approved_at'         => null,
                    'joined_at'           => Carbon::now()->toDateString(),

                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }
        });
    }
}
