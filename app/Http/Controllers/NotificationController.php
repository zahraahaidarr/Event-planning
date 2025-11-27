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
     * Automatically mark ALL unread notifications as read.
     */
    public function index()
    {
        $userId = Auth::id();

        // Fetch notifications first
        $notifications = DB::table('notifications')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Mark unread as read immediately when page opens
        DB::table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

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
     * (Optional) Keep this for API use but not needed anymore on UI.
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
     * Helper endpoint to create a test notification manually.
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
