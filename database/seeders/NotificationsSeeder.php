<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $now = now();

            // ✅ IDs from your screenshots
            $adminId   = 49;
            $workerIds = [51, 52, 53, 54, 55, 56, 57, 58];
            $clientIds = [59, 60];

            // ✅ Notifications data (matches your required features)
            $rows = [];

            /* 1) Workers notified when account status changes (Approved / Suspended) */
            $rows[] = [
                'user_id' => 51,
                'title' => 'Account status updated',
                'message' => 'Your account status was changed to APPROVED.',
                'type' => 'ACCOUNT',
            ];
            $rows[] = [
                'user_id' => 52,
                'title' => 'Account status updated',
                'message' => 'Your account status was changed to APPROVED.',
                'type' => 'ACCOUNT',
            ];
            $rows[] = [
                'user_id' => 56,
                'title' => 'Account status updated',
                'message' => 'Your account status was changed to SUSPENDED.',
                'type' => 'ACCOUNT',
            ];
            $rows[] = [
                'user_id' => 58,
                'title' => 'Account status updated',
                'message' => 'Your account status was changed to SUSPENDED.',
                'type' => 'ACCOUNT',
            ];

            /* 2) Users notified when sensitive credentials updated (password/email) */
            $rows[] = [
                'user_id' => 59,
                'title' => 'Security update: Email changed',
                'message' => 'Your email address was updated successfully. If you did not request this, please contact support.',
                'type' => 'ACCOUNT',
            ];
            $rows[] = [
                'user_id' => 60,
                'title' => 'Security update: Password changed',
                'message' => 'Your password was changed successfully. If you did not request this, please contact support.',
                'type' => 'ACCOUNT',
            ];

            /* 3) Workers & Clients notified when application/reservation submitted */
            $rows[] = [
                'user_id' => 53,
                'title' => 'Reservation submitted',
                'message' => 'Your reservation request was submitted successfully.',
                'type' => 'RESERVATION',
            ];
            $rows[] = [
                'user_id' => 59,
                'title' => 'New reservation submitted',
                'message' => 'A worker submitted a reservation for your event. Please review it.',
                'type' => 'RESERVATION',
            ];
            $rows[] = [
                'user_id' => 54,
                'title' => 'Application submitted',
                'message' => 'Your application was submitted successfully.',
                'type' => 'RESERVATION',
            ];
            $rows[] = [
                'user_id' => 60,
                'title' => 'New application received',
                'message' => 'A worker applied to your event. Please review the application.',
                'type' => 'RESERVATION',
            ];

            /* 4) Assigned workers notified when event edited/updated */
            $rows[] = [
                'user_id' => 55,
                'title' => 'Event updated',
                'message' => 'An event you are assigned to was updated. Please review the latest details.',
                'type' => 'EVENT',
            ];
            $rows[] = [
                'user_id' => 57,
                'title' => 'Event updated',
                'message' => 'An event you are assigned to was updated. Please review the latest details.',
                'type' => 'EVENT',
            ];

            /* 5) Assigned workers notified when event status changes */
            $rows[] = [
                'user_id' => 51,
                'title' => 'Event status changed',
                'message' => 'Event status has changed. Please check your dashboard for updates.',
                'type' => 'EVENT',
            ];
            $rows[] = [
                'user_id' => 52,
                'title' => 'Event status changed',
                'message' => 'Event status has changed. Please check your dashboard for updates.',
                'type' => 'EVENT',
            ];

            /* 6) Target users notified when announcement published */
            foreach (array_merge($workerIds, $clientIds) as $uid) {
                $rows[] = [
                    'user_id' => $uid,
                    'title' => 'New announcement published',
                    'message' => 'A new announcement has been published. Please open your dashboard to read it.',
                    'type' => 'ANNOUNCEMENT',
                ];
            }

            /* 7) Admin notified when user closes/deletes account */
            $rows[] = [
                'user_id' => $adminId,
                'title' => 'User account closed',
                'message' => 'A user has closed or deleted their account. Please review the record.',
                'type' => 'ACCOUNT',
            ];

            /* 8) Credited hours calculated for completed reservations */
            $rows[] = [
                'user_id' => 51,
                'title' => 'Credited hours updated',
                'message' => 'Your credited hours were calculated for a completed reservation.',
                'type' => 'RESERVATION',
            ];
            $rows[] = [
                'user_id' => 52,
                'title' => 'Credited hours updated',
                'message' => 'Your credited hours were calculated for a completed reservation.',
                'type' => 'RESERVATION',
            ];

            /* 9) Post-event report submission rules enforced */
            $rows[] = [
                'user_id' => 53,
                'title' => 'Post-event report required',
                'message' => 'You must submit your post-event report within the allowed time window.',
                'type' => 'REPORT',
            ];
            $rows[] = [
                'user_id' => 54,
                'title' => 'Post-event report reminder',
                'message' => 'Reminder: Your post-event report is still pending. Please submit it before the deadline.',
                'type' => 'REPORT',
            ];

            // ✅ Insert but avoid duplicates (same user_id + title + message + type)
            foreach ($rows as $r) {
                $exists = DB::table('notifications')
                    ->where('user_id', $r['user_id'])
                    ->where('title', $r['title'])
                    ->where('message', $r['message'])
                    ->where('type', $r['type'])
                    ->exists();

                if ($exists) continue;

                DB::table('notifications')->insert([
                    'user_id'    => $r['user_id'],
                    'title'      => $r['title'],
                    'message'    => $r['message'],
                    'type'       => $r['type'],
                    'is_read'    => 0,
                    'created_at' => $now,
                ]);
            }
        });
    }
}
