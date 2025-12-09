<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
use App\Services\Notify;
use App\Models\Worker;
use App\Models\WorkerReservation;
use App\Models\Event;
use App\Models\Employee;


class ProfileController extends Controller
{
    public function show()
    {
        $u = Auth::user();

        // Blade file is resources/views/worker/profile.blade.php
        return view('profile', compact('u'));
    }

    public function data()
    {
        $u = Auth::user();

        return response()->json([
            'id'             => $u->id,
            'first_name'     => $u->first_name,
            'last_name'      => $u->last_name,
            'full_name'      => $u->full_name,
            'email'          => $u->email,
            'username'       => $u->email,
            'role'           => $u->role,
            'phone'          => $u->phone,
            'date_of_birth'  => optional($u->date_of_birth)->toDateString(),
            'created_at'     => optional($u->created_at)->toDateTimeString(),
            'last_login_at'  => optional($u->last_login_at)->toDateTimeString(),
            'avatar_url'     => $u->avatar_path ? Storage::url($u->avatar_path) : null,
        ]);
    }

    public function updateAccount(Request $r)
    {
        $u = Auth::user();

        // store old email BEFORE updating
        $oldEmail = $u->email;

        $data = $r->validate([
            'first_name' => ['required','string','max:255'],
            'last_name'  => ['nullable','string','max:255'],
            'email'      => [
                'required',
                'email',
                'max:255',
                Rule::unique('users','email')->ignore($u->id),
            ],
        ]);

        $u->first_name = $data['first_name'];
        $u->last_name  = $data['last_name'] ?? null;
        $u->email      = $data['email'];
        $u->save();

        // notify only if email actually changed
        if ($oldEmail !== $u->email) {
            Notify::to(
                $u->id,
                'Email changed',
                'Your account email address was just changed. If this was not you, please contact support immediately.',
                'SECURITY'
            );
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Account updated successfully.',
        ]);
    }

    public function updatePersonal(Request $r)
    {
        $u = Auth::user();

        $data = $r->validate([
            'phone'         => ['nullable','string','max:50'],
            'date_of_birth' => ['nullable','date','before_or_equal:today'],
        ]);

        $u->phone         = $data['phone'] ?? null;
        $u->date_of_birth = $data['date_of_birth'] ?? null;
        $u->save();

        return response()->json([
            'ok'      => true,
            'message' => 'Personal info updated successfully.',
        ]);
    }

    public function updatePassword(Request $r)
    {
        $u = Auth::user();

        $r->validate([
            'current_password' => ['required'],
            'password'         => ['required','confirmed', Password::min(8)],
        ]);

        if (!Hash::check($r->input('current_password'), $u->password)) {
            return response()->json([
                'ok'      => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $u->password = Hash::make($r->input('password'));
        $u->save();

        Notify::to(
            $u->id,
            'Password changed',
            'Your account password was just changed. If this was not you, please contact support immediately.',
            'SECURITY'
        );

        return response()->json([
            'ok'      => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    public function uploadAvatar(Request $r)
    {
        $u = Auth::user();

        $r->validate([
            'avatar' => ['required','image','mimes:jpg,jpeg,png','max:2048'],
        ]);

        // delete old avatar if exists
        if ($u->avatar_path) {
            Storage::disk('public')->delete($u->avatar_path);
        }

        // store new avatar in storage/app/public/avatars
        $path = $r->file('avatar')->store('avatars', 'public');

        $u->avatar_path = $path;
        $u->save();

        return response()->json([
            'ok'         => true,
            'avatar_url' => Storage::url($path),
        ]);
    }

public function updateEngagement(Request $r)
{
    $u = Auth::user();

    // Only workers are allowed to change engagement
    if (strtoupper($u->role ?? '') !== 'WORKER') {
        return response()->json([
            'ok'      => false,
            'message' => 'Only workers can change engagement.',
        ], 403);
    }

    $data = $r->validate([
        'is_volunteer'    => ['required','boolean'],
        'engagement_kind' => ['required','in:VOLUNTEER,PAID'],
        'hourly_rate'     => ['nullable','numeric','min:0'],
    ]);

    // Find worker by user_id
    $worker = Worker::firstOrNew(['user_id' => $u->id]);

    // Old values to detect change
    $oldIsVolunteer = $worker->exists ? (int) $worker->is_volunteer : null;
    $oldRate        = $worker->exists ? $worker->hourly_rate      : null;

    if (! $worker->exists) {
        $worker->total_hours         = 0;
        $worker->verification_status = 'PENDING';
    }

    $worker->is_volunteer    = $data['is_volunteer'];
    $worker->engagement_kind = $data['is_volunteer'] ? 'VOLUNTEER' : 'PAID';

    // If volunteer -> null; if paid -> use provided rate
    $worker->hourly_rate = $worker->is_volunteer
        ? null
        : ($data['hourly_rate'] ?? 0);

    $worker->save();

    /* ============ Notify event owners if it changed ============ */

/* ----------------- Notify event owners if it changed ----------------- */

// Did anything relevant change?
$statusChanged = $oldIsVolunteer !== null && $oldIsVolunteer !== (int) $worker->is_volunteer;
$rateChanged   = ! $worker->is_volunteer && $oldRate !== $worker->hourly_rate;

if ($statusChanged || $rateChanged) {

    // Consider all reservations that are NOT cancelled/completed
    $reservations = WorkerReservation::where('worker_id', $worker->worker_id)
        ->whereNotIn('status', ['CANCELLED', 'COMPLETED'])
        ->get();

    $fromStatus = $oldIsVolunteer === 1 ? 'VOLUNTEER' : 'PAID';
    $toStatus   = $worker->is_volunteer ? 'VOLUNTEER' : 'PAID';

    // Group events by owner user id
    $ownersEvents = [];   // [ownerUserId => [event_id => title]]

    foreach ($reservations as $res) {
        $event = Event::find($res->event_id);
        if (! $event) {
            continue;
        }

        // events.created_by -> employees.employee_id -> employees.user_id
        $employee    = Employee::find($event->created_by);
        $ownerUserId = $employee?->user_id;

        if (! $ownerUserId) {
            continue;
        }

        // Use event_id as key to avoid duplicates
        $ownersEvents[$ownerUserId][$event->event_id] = $event->title;
    }

    if (! empty($ownersEvents)) {

        if ($worker->is_volunteer) {
            $rateText = 'as a volunteer (no hourly rate).';
        } else {
            $rateText = 'with an hourly rate of '.$worker->hourly_rate.' per hour.';
        }

        $workerName = trim(($u->first_name ?? '').' '.($u->last_name ?? '')) ?: $u->email;
        $title      = 'Worker engagement status changed';

        foreach ($ownersEvents as $ownerUserId => $events) {
            $titles = array_values($events);

            if (count($titles) === 1) {
                $eventsText = "for your event '".$titles[0]."'.";
            } else {
                $eventsText = "for your events: '".implode("', '", $titles)."'.";
            }

            $message = sprintf(
                "%s changed their engagement from %s to %s %s This worker has reservations %s",
                $workerName,
                $fromStatus,
                $toStatus,
                $rateText,
                $eventsText
            );

            Notify::to(
                $ownerUserId,
                $title,
                $message,
                'RESERVATION'
            );
        }
    }
}


    return response()->json([
        'ok'      => true,
        'message' => 'Engagement updated successfully.',
    ]);
}



}
