<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkerReservation;
use App\Models\Worker;
use App\Models\Event;
use App\Models\Venue;
use App\Models\WorkRole;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Automatically mark past reservations as COMPLETED
     * when the event's ends_at time has passed.
     */
       protected function autoCompletePastReservations($worker): void
    {
        $now = Carbon::now();

        $reservations = WorkerReservation::with('event')
            ->where('worker_id', $worker->worker_id)
            ->whereIn('status', ['RESERVED', 'CHECKED_IN', 'CHECKED_OUT'])
            ->get();

        foreach ($reservations as $reservation) {
            $event = $reservation->event;

            if (! $event || ! $event->ends_at) {
                continue;
            }

            $endsAt = $event->ends_at instanceof Carbon
                ? $event->ends_at
                : Carbon::parse($event->ends_at);

            if ($endsAt->gt($now)) {
                continue;
            }

            // --- event is in the past: mark reservation as COMPLETED ---
            $reservation->status = 'COMPLETED';

            if ($event->starts_at && $event->ends_at) {
                $reservation->check_in_time  = $reservation->check_in_time ?: $event->starts_at;
                $reservation->check_out_time = $event->ends_at;

                $minutes = $event->starts_at->diffInMinutes($event->ends_at);
                $reservation->credited_hours = round($minutes / 60, 2);
            } else {
                if (! $reservation->check_in_time) {
                    $reservation->check_in_time = $reservation->reserved_at ?? $now;
                }

                $reservation->check_out_time = $now;

                $minutes = Carbon::parse($reservation->check_in_time)
                    ->diffInMinutes($reservation->check_out_time);

                $reservation->credited_hours = round($minutes / 60, 2);
            }

            $reservation->save();

            // 🔁 ALSO update the EVENT status to COMPLETED
            if ($event->status !== 'CANCELLED' && $event->status !== 'COMPLETED') {
                $event->status = 'COMPLETED';
                $event->save();
            }
        }
    }

public function complete(WorkerReservation $reservation, Request $request)
{
    $user   = $request->user();
    $worker = Worker::where('user_id', $user->id)->first();

    if (! $worker || $reservation->worker_id !== $worker->worker_id) {
        return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
    }

    $event = $reservation->event;

    if (! $event || ! $event->ends_at || $event->ends_at->isFuture()) {
        return response()->json([
            'ok'      => false,
            'message' => 'Event has not finished yet.',
        ], 422);
    }

    // Auto-complete it
    $reservation->status = 'COMPLETED';
    $reservation->check_in_time  = $reservation->check_in_time ?: $event->starts_at;
    $reservation->check_out_time = $event->ends_at;

    $minutes = Carbon::parse($event->starts_at)->diffInMinutes($event->ends_at);
    $reservation->credited_hours = round($minutes / 60, 2);

    $reservation->save();

    return response()->json([
        'ok'      => true,
        'message' => 'Reservation automatically marked as completed.',
    ]);
}

    /**
     * Show reservations for the logged-in worker.
     */
    public function index(Request $request)
    {
        $user   = $request->user();
        $worker = Worker::where('user_id', $user->id)->first();

        if (! $worker) {
            abort(403, 'Worker profile not found for this user.');
        }

        // ✅ First, auto-complete any past events
        $this->autoCompletePastReservations($worker);

        // Now load fresh reservations after auto-update
        $reservations = WorkerReservation::with(['event', 'workRole'])
            ->where('worker_id', $worker->worker_id)
            ->latest()
            ->get();

        // ===== STATS =====
        $now          = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth   = $now->copy()->endOfMonth();

        $totalApplications = $reservations->count();

        $applicationsThisMonth = $reservations->filter(function ($r) use ($startOfMonth, $endOfMonth) {
            return $r->reserved_at &&
                Carbon::parse($r->reserved_at)->between($startOfMonth, $endOfMonth);
        })->count();

        $reservedCount = $reservations->filter(function ($r) {
            return strtolower($r->status) === 'reserved';
        })->count();

        $completedCount = $reservations->filter(function ($r) {
            return strtolower($r->status) === 'completed';
        })->count();

        $completedThisMonth = $reservations->filter(function ($r) use ($startOfMonth, $endOfMonth) {
            return strtolower($r->status) === 'completed'
                && $r->check_out_time
                && Carbon::parse($r->check_out_time)->between($startOfMonth, $endOfMonth);
        })->count();

        $stats = [
            'totalApplications'     => $totalApplications,
            'applicationsThisMonth' => $applicationsThisMonth,
            'reservedCount'         => $reservedCount,
            'completedCount'        => $completedCount,
            'completedThisMonth'    => $completedThisMonth,
        ];

        // ===== TRANSFORM FOR FRONTEND =====
        $applications = $reservations->map(function (WorkerReservation $res) {
            $event = $res->event;

            // Duration logic
            $plannedHours = $event->duration_hours ?? $event->hours ?? null;

            if (!is_null($plannedHours)) {
                $durationText = number_format((float) $plannedHours, 2) . ' hours';
            } elseif (!empty($res->credited_hours)) {
                $durationText = number_format((float) $res->credited_hours, 2) . ' hours';
            } elseif ($res->check_in_time && $res->check_out_time) {
                $checkIn  = Carbon::parse($res->check_in_time);
                $checkOut = Carbon::parse($res->check_out_time);
                $hours    = $checkIn->diffInMinutes($checkOut) / 60;
                $durationText = number_format($hours, 2) . ' hours';
            } else {
                $durationText = 'N/A';
            }

            return [
                'id'         => $res->reservation_id,
                'eventTitle' => $event->title
                                 ?? $event->name
                                 ?? 'Untitled Event',
                'role'       => optional($res->workRole)->role_name ?? 'Volunteer',
                'status'     => strtolower($res->status),

                'date' => $event->date
                          ?? optional($event?->starts_at)->format('Y-m-d')
                          ?? null,

                'time' => $event->time
                          ?? optional($event?->starts_at)->format('H:i')
                          ?? null,

                'location' => optional($event?->venue)->name
                              ?? $event->location
                              ?? '—',

                'appliedDate' => $res->reserved_at
                    ? Carbon::parse($res->reserved_at)->format('Y-m-d H:i')
                    : null,

                'duration'        => $durationText,
                'rejectionReason' => $res->rejection_reason ?? null,
            ];
        })->values();

        return view('worker.my-reservations', [
            'reservationsBootstrap' => $applications,
            'stats'                 => $stats,
        ]);
    }

    // cancel() stays as you already have it
public function cancel($id, Request $request)
{
    $user   = $request->user();
    $worker = Worker::where('user_id', $user->id)->first();

    if (! $worker) {
        return response()->json([
            'ok'      => false,
            'message' => 'Worker profile not found.',
        ], 403);
    }

    // Find only THIS worker's reservation
    $reservation = WorkerReservation::where('reservation_id', $id)
        ->where('worker_id', $worker->worker_id)
        ->first();

    if (! $reservation) {
        return response()->json([
            'ok'      => false,
            'message' => 'Reservation not found.',
        ], 404);
    }

    // Optional: block cancelling completed ones
    if (strtoupper($reservation->status) === 'COMPLETED') {
        return response()->json([
            'ok'      => false,
            'message' => 'You cannot cancel a completed reservation.',
        ], 422);
    }

    // Update ONLY this reservation
    $reservation->status         = 'CANCELLED';
    $reservation->check_out_time = now(); // optional
    $reservation->save();

    return response()->json([
        'ok'       => true,
        'message'  => 'Reservation cancelled successfully',
        'status'   => 'CANCELLED',
        'uiStatus' => 'cancelled',
    ]);
}



    // You can now remove markCompleted() if you don't use it anymore.
}
