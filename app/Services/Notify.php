<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\SystemSetting;
use Illuminate\Support\Str;

class Notify
{
    /**
     * Create a notification IF the user's settings allow it.
     */
    public static function to(int $userId, string $title, string $message, string $type = 'GENERAL'): ?Notification
    {
        $type = strtoupper($type);

        // Check user settings before creating the notification
        if (! self::userWantsNotification($userId, $type)) {
            return null; // User disabled this type of notification
        }

        // Normal creation (same as your original)
        return Notification::create([
            'user_id'    => $userId,
            'title'      => Str::limit($title, 120),
            'message'    => $message,
            'type'       => $type,
            'is_read'    => false,
            'created_at' => now(),
        ]);
    }

    /**
     * Determine if user allows this notification type based on system_settings.
     */
    protected static function userWantsNotification(int $userId, string $type): bool
    {
        $prefix = "worker:{$userId}:";

        // These are all the keys we need from DB
        $keys = [
            $prefix.'notifications_app',
            $prefix.'notifications_announcements',
            $prefix.'notifications_chat',
            $prefix.'notifications_event_reminders',
        ];

        $settings = SystemSetting::whereIn('key', $keys)
            ->pluck('value', 'key');

        // ========== MASTER SWITCH ==========
        $appEnabled = ($settings[$prefix.'notifications_app'] ?? '1') === '1';
        if (! $appEnabled) {
            return false; // user disabled all notifications
        }

        // ========== PER-NOTIFICATION MAPPING ==========
        $map = [
            'ANNOUNCEMENT'   => 'notifications_announcements',
            'CHAT'           => 'notifications_chat',
            'EVENT_STATUS'   => 'notifications_event_reminders',
            'EVENT_REMINDER' => 'notifications_event_reminders',
            'RESERVATION'    => 'notifications_event_reminders',

            // these are always allowed if general app notifications are on:
            'ACCOUNT'        => null,
            'SECURITY'       => null,
        ];

        $settingKey = $map[$type] ?? null;

        // No per-type setting? Only respect master switch.
        if ($settingKey === null) {
            return true;
        }

        // Specific toggle ON or OFF
        $specific = $settings[$prefix.$settingKey] ?? '1';

        return $specific === '1';
    }
}
