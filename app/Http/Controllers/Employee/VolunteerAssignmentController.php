<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use App\Models\Employee;
use App\Models\WorkerReservation;

class VolunteerAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $employeeId = Employee::where('user_id', $user->id)->value('employee_id');
        if (! $employeeId) {
            abort(403, 'Employee profile not found for this user.');
        }

        $events = Event::where('created_by', $employeeId)
            ->orderByDesc('starts_at')
            ->get(['event_id', 'title', 'starts_at']);

        return view('Employee.volunteer-assignment', [
            'events' => $events,
        ]);
    }

    /**
     * AJAX:
     * GET /employee/volunteer-assignment/events/{event}/applications
     *
     * Returns reservations for the selected event (only if created by this employee).
     */
    public function applications(Request $request, $eventId)
    {
        $user = $request->user();

        // --------- which employee is logged in? ----------
        $employeeId = Employee::where('user_id', $user->id)->value('employee_id');
        if (! $employeeId) {
            abort(403, 'Employee profile not found.');
        }

        // --------- make sure this event belongs to this employee ----------
        $event = Event::where('event_id', $eventId)
            ->where('created_by', $employeeId)
            ->firstOrFail();

        // --------- load all reservations for this event ----------
        $reservations = WorkerReservation::with(['worker.user', 'workRole'])
            ->where('event_id', $event->event_id)
            ->orderByDesc('reserved_at')
            ->get();

        // If no reservations, just return empty list (prevents JS errors)
        if ($reservations->isEmpty()) {
            return response()->json([
                'ok'           => true,
                'stats'        => [
                    'total'    => 0,
                    'pending'  => 0,
                    'rejected' => 0,
                ],
                'applications' => [],
            ]);
        }

        // --------- pre-calculate "previous events" per worker ----------
        $workerIds = $reservations->pluck('worker_id')->filter()->unique();

        $completedCounts = WorkerReservation::select('worker_id', DB::raw('COUNT(*) as cnt'))
            ->whereIn('worker_id', $workerIds)
            ->where('status', 'COMPLETED')
            ->groupBy('worker_id')
            ->pluck('cnt', 'worker_id');     // [worker_id => completed_count]

        // --------- map DB rows -> JSON structure expected by JS ----------
        $apps = $reservations->map(function (WorkerReservation $r) use ($completedCounts) {
            $worker = $r->worker;
            $user   = $worker?->user;
            $role   = $r->workRole;

            // map DB status -> UI status
            $rawStatus = strtoupper($r->status ?? 'PENDING');

$statusMap = [
    'PENDING'   => 'pending',   // new app: waiting decision
    'RESERVED'  => 'accepted', 
    'REJECTED'  => 'rejected', // after employee accepts
    
    'COMPLETED' => 'completed',
];

$status = $statusMap[$rawStatus] ?? strtolower($rawStatus);

            // date of application
            $date = $r->reserved_at ?? $r->created_at;
            $dateStr = $date ? $date->format('Y-m-d') : null;

            // experience: derive from joined_at in workers table if it exists
            $joinedAt        = $worker?->joined_at;
            $experienceYears = $joinedAt ? $joinedAt->diffInYears(now()) : null;
            $experienceLabel = $experienceYears ? $experienceYears . ' years' : null;

            // how many completed events this worker already has
            $previousEvents = 0;
            if ($worker && $completedCounts->has($worker->worker_id)) {
                $previousEvents = (int) $completedCounts[$worker->worker_id];
            }

            // skills + availability: if you don't have them in DB yet, they will be null
            // you can later replace null with real queries when you add those tables/columns
            $skills       = null;
            $availability = null;

            return [
                'id'             => $r->reservation_id,
                'volunteerId'    => $worker?->worker_id,
                'name'           => $user?->name ?? 'Unknown',
                'email'          => $user?->email ?? null,
                'phone'          => $user->phone ?? null,  // if column does not exist => null
                'role'           => $role?->role_name ?? null,
                'appliedDate'    => $dateStr,
                'status'         => $status,

                'experience'     => $experienceLabel,
                'skills'         => $skills,
                'availability'   => $availability,
                'previousEvents' => $previousEvents,
            ];
        });

        $total    = $apps->count();
        $pending  = $apps->where('status', 'pending')->count();
        $rejected = $apps->where('status', 'rejected')->count();

        return response()->json([
            'ok'           => true,
            'stats'        => [
                'total'    => $total,
                'pending'  => $pending,
                'rejected' => $rejected,
            ],
            'applications' => $apps->values(),
        ]);
    }

    public function updateStatus(Request $request, WorkerReservation $reservation)
{
    $user = $request->user();

    // make sure this user is an employee
    $employeeId = Employee::where('user_id', $user->id)->value('employee_id');
    if (! $employeeId) {
        return response()->json([
            'ok'      => false,
            'message' => 'Employee profile not found.',
        ], 403);
    }

    // make sure the reservation belongs to an event created by this employee
    $event = Event::where('event_id', $reservation->event_id)
        ->where('created_by', $employeeId)
        ->first();

    if (! $event) {
        return response()->json([
            'ok'      => false,
            'message' => 'You are not allowed to modify this reservation.',
        ], 403);
    }

    // validate desired status
   $data = $request->validate([
    'status' => 'required|in:PENDING,RESERVED,CHECKED_IN,CHECKED_OUT,COMPLETED,NO_SHOW,CANCELLED,REJECTED',
]);

    $reservation->status = $data['status'];
    $reservation->save();

    // backend -> UI status mapping (same as in applications())
    $statusMap = [
        'PENDING'   => 'pending',
        'RESERVED'  => 'accepted',
        'REJECTED'  => 'rejected',
        'COMPLETED' => 'completed',
    ];
    $uiStatus = $statusMap[$reservation->status] ?? strtolower($reservation->status);

    return response()->json([
        'ok'       => true,
        'status'   => $reservation->status,
        'uiStatus' => $uiStatus,
    ]);
}

}
