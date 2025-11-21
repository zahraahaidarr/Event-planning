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
     * Show reservations for the logged-in worker.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Find worker row for this user
        $worker = Worker::where('user_id', $user->id)->first();

        if (! $worker) {
            abort(403, 'Worker profile not found for this user.');
        }

        // Base query
        $reservationsQuery = WorkerReservation::with(['event', 'workRole'])
            ->where('worker_id', $worker->worker_id)
            ->latest();

        $reservations = $reservationsQuery->get();

        // ===== STATS =====
        $now          = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth   = $now->copy()->endOfMonth();

        // Total
        $totalApplications = $reservations->count();

        // Total this month (using reserved_at)
        $applicationsThisMonth = $reservations->filter(function ($r) use ($startOfMonth, $endOfMonth) {
            return $r->reserved_at &&
                Carbon::parse($r->reserved_at)->between($startOfMonth, $endOfMonth);
        })->count();

        // Reserved count
        $reservedCount = $reservations->filter(function ($r) {
            return strtolower($r->status) === 'reserved';
        })->count();

        // Completed count
        $completedCount = $reservations->filter(function ($r) {
            return strtolower($r->status) === 'completed';
        })->count();

        // Completed this month (using check_out_time)
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

                // Date & Time
                'date' => $event->date
                          ?? optional($event->starts_at)->format('Y-m-d')
                          ?? null,

                'time' => $event->time
                          ?? optional($event->starts_at)->format('H:i')
                          ?? null,

                // Venue or location
                'location' => optional($event->venue)->name
                              ?? $event->location
                              ?? '—',

                // Application datetime
                'appliedDate' => $res->reserved_at
                    ? Carbon::parse($res->reserved_at)->format('Y-m-d H:i')
                    : null,

                // Duration
                'duration' => $durationText,

                // Rejection reason
                'rejectionReason' => $res->rejection_reason ?? null,
            ];
        })->values();

        return view('worker.my-reservations', [
            'reservationsBootstrap' => $applications,
            'stats'                 => $stats,
        ]);
    }

    /**
     * Worker cancels a reservation (hard delete).
     */
    public function cancel(WorkerReservation $reservation, Request $request)
    {
        $user = $request->user();
        $worker = Worker::where('user_id', $user->id)->first();

        if (! $worker || $reservation->worker_id !== $worker->worker_id) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        $reservation->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Reservation cancelled successfully',
        ]);
    }

    /**
     * Worker marks a reservation as completed.
     */
    public function markCompleted(WorkerReservation $reservation, Request $request)
    {
        $user = $request->user();
        $worker = Worker::where('user_id', $user->id)->first();

        if (! $worker || $reservation->worker_id !== $worker->worker_id) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }

        if (strtolower($reservation->status) === 'completed') {
            return response()->json([
                'ok'      => true,
                'message' => 'This reservation is already marked as completed.',
            ]);
        }

        $reservation->status = 'COMPLETED';
        $reservation->check_out_time = now();

        if (! $reservation->check_in_time) {
            $reservation->check_in_time = $reservation->reserved_at ?? now();
        }

        $reservation->save();

        return response()->json([
            'ok'      => true,
            'message' => 'Reservation marked as completed.',
        ]);
    }
}
