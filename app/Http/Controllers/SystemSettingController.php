<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Worker;
use App\Models\WorkerReservation;
use App\Models\Notification;


class SystemSettingController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        // Default values if nothing is saved yet
        $defaults = [
            'notifications_app'             => '1',
            'notifications_announcements'   => '1',
            'notifications_chat'            => '1',
            'notifications_event_reminders' => '1',
            'ui_language'                   => app()->getLocale() === 'ar' ? 'ar' : 'en',
            'ui_theme'                      => 'dark',
        ];

        $prefix = "worker:{$user->id}:";

        $dbSettings = SystemSetting::whereIn(
                'key',
                array_map(fn ($k) => $prefix.$k, array_keys($defaults))
            )
            ->pluck('value', 'key');

        $settings = [];
        foreach ($defaults as $key => $default) {
            $fullKey = $prefix.$key;
            $settings[$key] = $dbSettings[$fullKey] ?? $default;
        }

        return view('settings', compact('settings'));
    }

    /**
     * Save settings via AJAX.
     * Expect payload: { settings: { key: value, ... } }
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->input('settings', []);

        if (!is_array($data)) {
            return response()->json(['message' => 'Invalid payload'], 422);
        }

        $prefix = "worker:{$user->id}:";

        DB::transaction(function () use ($prefix, $data) {
            foreach ($data as $key => $value) {
                $fullKey = $prefix.$key;

                SystemSetting::updateOrCreate(
                    ['key' => $fullKey],
                    [
                        'value'      => (string) $value,
                        'updated_at' => now(),
                    ]
                );
            }
        });

        return response()->json(['message' => 'Settings saved']);
    }

    /**
     * Logout from all other devices (database session driver).
     */
    public function logoutAll(Request $request)
{
    $user = $request->user();

    // Delete ALL sessions belonging to this user
    DB::table('sessions')
        ->where('user_id', $user->id)
        ->delete();

    // Log out the current user explicitly
    Auth::logout();

    // Invalidate current session
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Redirect to login page
    return redirect()->route('login')->with('status', 'Logged out from all devices.');
}


    /**
     * Delete the current user's account.
     */
   public function destroyAccount(Request $request)
{
    $user = $request->user();

    DB::transaction(function () use ($user) {

        // 1) If user is a WORKER, cancel only FUTURE reservations
        if (method_exists($user, 'worker') && $user->worker) {
            $worker = $user->worker;

            \App\Models\WorkerReservation::where('worker_id', $worker->worker_id)
                ->whereHas('event', function ($q) {
                    $q->where('starts_at', '>', now());
                })
                ->update(['status' => 'CANCELLED']);
            // We DO NOT delete the worker row – we keep history
        }

        // 2) If user is an EMPLOYEE or ADMIN: we also keep their
        //    employee row + events + announcements for history.
        //    (No extra deletes here.)

        // 3) Delete in-app notifications & per-user settings (optional)
        \App\Models\Notification::where('user_id', $user->id)->delete();
        \App\Models\SystemSetting::where('key', 'like', "worker:{$user->id}:%")->delete();

        // 4) Finally: mark account as "dead"
        $user->status = 'BANNED';  // or 'INACTIVE' if you add that
        $user->save();
    });
$admins = \App\Models\User::where('role', 'ADMIN')->pluck('id');

foreach ($admins as $adminId) {
    \App\Services\Notify::to(
        $adminId,
        "User Account Closed",
        "{$user->first_name} {$user->last_name} ({$user->role}) has closed their account.",
        'ACCOUNT'
    );
}
    // Logout + invalidate session
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login')->with('status', 'Your account has been closed.');
}

}
