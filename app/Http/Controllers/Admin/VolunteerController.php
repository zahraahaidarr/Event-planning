<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Notify;
use App\Models\WorkerReservation;
use Carbon\Carbon;

class VolunteerController extends Controller
{
    /* ======================= Views & JSON ======================= */

    public function index()
    {
        $initial = $this->baseQuery()->get();
        $volunteers = $this->normalize($initial);

        return view('Admin.volunteers', [
            'volunteers' => $volunteers,
        ]);
    }

    public function list()
    {
        $rows = $this->baseQuery()->get();
        return response()->json($this->normalize($rows));
    }

    /**
     * Filters:
     *  - q: search in user name/email (optional)
     *  - role: exact role type name (optional)
     *  - location: exact location (optional)
     *  - status: active|suspended|banned|pending (maps to users.status)
     */
    public function search(Request $request)
    {
        $q = $this->baseQuery();

        // free-text (name/email)
        if ($term = trim((string) $request->input('q'))) {
            $q->whereHas('user', function ($uq) use ($term) {
                $uq->where('name', 'like', "%{$term}%")
                   ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if ($loc = $request->string('location')->trim()) {
            $q->where('location', $loc);
        }

        if ($role = $request->string('role')->trim()) {
            $q->whereHas('reservations.workRole.roleType', fn($qq) => $qq->where('name', $role));
        }

        if ($status = $request->string('status')->trim()) {
            // map incoming (lowercase) to DB values on users.status
            $map = [
                'active'    => 'ACTIVE',
                'suspended' => 'SUSPENDED',
                'banned'    => 'BANNED',
                'pending'   => 'PENDING',
            ];
            if (isset($map[$status])) {
                $dbStatus = $map[$status];
                $q->whereHas('user', fn($uq) => $uq->where('status', $dbStatus));
            }
        }

        $rows = $q->get();
        return response()->json($this->normalize($rows));
    }

    /* ======================= Mutations ======================= */

    /**
     * POST /admin/volunteers/{id}/status
     * Body: { status: 'ACTIVE' | 'SUSPENDED' | 'BANNED' | 'PENDING' }
     * Updates ONLY users.status for the worker's linked user.
     */
    public function setStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'required|string|in:ACTIVE,SUSPENDED,BANNED,PENDING',
        ]);

        $worker = Worker::with('user:id,status')->find($id);
        if (!$worker || !$worker->user) {
            return response()->json([
                'ok' => false,
                'message' => 'Worker or linked user not found',
            ], Response::HTTP_NOT_FOUND);
        }

        DB::transaction(function () use ($worker, $request) {
            $worker->user->status = $request->input('status'); // ACTIVE/SUSPENDED/BANNED/PENDING
            $worker->user->save();
        });
        Notify::to(
    $worker->user->id,
    'Account status updated',
    "Your account status was changed to {$request->input('status')}.",
    'ACCOUNT'
);


        return response()->json([
            'ok' => true,
            'message' => 'Status updated.',
        ]);
    }
public function completeReservation($reservationId)
{
    $reservation = WorkerReservation::with('event')->findOrFail($reservationId);
    $event = $reservation->event;

    if (!$event) {
        abort(500, 'Event not found');
    }

    // ---- 1) Fix missing check-in time ----
    if (!$reservation->check_in_time) {
        // Use event start time
        $reservation->check_in_time = $event->starts_at;
    }

    // ---- 2) Fix missing check-out time ----
    if (!$reservation->check_out_time) {
        // Use event end time
        $reservation->check_out_time = $event->ends_at;
    }

    // ---- 3) Calculate hours ----
    $checkIn  = Carbon::parse($reservation->check_in_time);
    $checkOut = Carbon::parse($reservation->check_out_time);

    // Prevent negative or unrealistic values
    if ($checkOut->lessThan($checkIn)) {
        $checkOut = $checkIn->copy()->addHours($event->duration_hours ?? 1);
    }

    $minutes = $checkIn->diffInMinutes($checkOut);
    $reservation->credited_hours = round($minutes / 60, 2);

    // ---- 4) Mark as completed ----
    $reservation->status = 'COMPLETED';
    $reservation->save();
}



    /* ======================= Internals ======================= */

private function baseQuery()
{
    return Worker::query()
        ->with([
            'user:id,first_name,last_name,email,status',
            'reservations.workRole.roleType:role_type_id,name',
        ])

        // Count ONLY reservations that are NOT cancelled
        ->withCount([
            'reservations as reservations_count' => function ($q) {
                $q->where('status', '!=', 'CANCELLED');
            },
        ])

        // SUM credited_hours ONLY for completed reservations
        ->withSum([
            'reservations as total_hours' => function ($q) {
                $q->where('status', 'COMPLETED');
            }
        ], 'credited_hours');
}



    private function normalize($collection)
    {
        return $collection->map(function ($w) {
            $roleName   = $w->reservations->first()?->workRole?->roleType?->name ?? '';
            $userStatus = strtoupper((string) ($w->user->status ?? 'PENDING'));

            $statusForUi = match ($userStatus) {
                'ACTIVE'    => 'active',
                'SUSPENDED' => 'suspended',
                'BANNED'    => 'banned',
                'PENDING'   => 'pending',
                default     => 'pending',
            };

            return [
                'id'        => $w->worker_id,
                'name'      => $w->user->name ?? '',
                'email'     => $w->user->email ?? '',
                'role'      => $roleName,
                'location'  => $w->location ?? '',
                'events'    => (int) ($w->reservations_count ?? 0),
                'hours'     => (float) ($w->total_hours ?? 0),
                'status'    => $statusForUi, // active|suspended|banned|pending
            ];
        });
    }
}
