<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the logged-in user.
     */
    public function index()
    {
        $userId = Auth::id();

        // Fetch the latest 50 notifications
        $notifications = DB::table('notifications')
    ->where('user_id', $userId)
    ->orderBy('created_at', 'desc')
    ->paginate(15);   // << now it's a LengthAwarePaginator


        return view('notifications.index', compact('notifications'));
    }

    /**
     * Return unread notifications count for the floating bell (AJAX).
     */
    public function unreadCount(): JsonResponse
    {
        $userId = Auth::id();

        $count = DB::table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark all notifications as read (AJAX).
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $userId = Auth::id();

        DB::table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    /**
     * Helper endpoint to create a test notification manually (optional).
     * You can remove this in production.
     */
    public function createTestNotification(): JsonResponse
    {
        $userId = Auth::id();

        DB::table('notifications')->insert([
            'user_id'    => $userId,
            'title'      => 'Test Notification',
            'message'    => 'This is a test notification for your account.',
            'type'       => 'system',
            'is_read'    => false,
            'created_at' => now(),
        ]);

        return response()->json(['ok' => true, 'message' => 'Notification created']);
    }
}
