<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Str;

class Notify
{
    public static function to(int $userId, string $title, string $message, string $type = 'GENERAL'): Notification
    {
        return Notification::create([
            'user_id'    => $userId,
            'title'      => Str::limit($title, 120),
            'message'    => $message,
            'type'       => strtoupper($type),
            'is_read'    => false,
            'created_at' => now(),
        ]);
    }
}
