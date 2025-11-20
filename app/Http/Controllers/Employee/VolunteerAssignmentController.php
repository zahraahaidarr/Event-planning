<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
     * AJAX: GET /employee/volunteer-assignment/events/{event}/applications
     * Returns reservations for the selected event (only if created by this employee).
     */
public function applications(Request $request, Event $event)
{
    $user = $request->user();

    $employeeId = Employee::where('user_id', $user->id)->value('employee_id');
    if (! $employeeId) {
        abort(403, 'Employee profile not found.');
    }

    // security: only events created by this employee
    if ((int) $event->created_by !== (int) $employeeId) {
        abort(403, 'You are not allowed to view applications for this event.');
    }

    $reservations = WorkerReservation::with(['worker.user', 'workRole'])
        ->where('event_id', $event->event_id)   // event_id from table
        ->orderByDesc('reserved_at')
        ->get();

    $apps = $reservations->map(function (WorkerReservation $r) {
        $worker = $r->worker;
        $user   = $worker?->user;
        $role   = $r->workRole;

        $rawStatus = strtoupper($r->status ?? 'RESERVED');
        switch ($rawStatus) {
            case 'RESERVED':
                $status = 'pending';
                break;
            case 'CANCELLED':
                $status = 'rejected';
                break;
            default:
                $status = strtolower($rawStatus);
        }

        $date = $r->reserved_at ?? $r->created_at;
        $dateStr = $date ? \Illuminate\Support\Carbon::parse($date)->format('Y-m-d') : null;

        return [
            'id'             => $r->reservation_id,
            'volunteerId'    => $worker?->worker_id,
            'name'           => $user?->name ?? 'Unknown',
            'email'          => $user?->email ?? null,
            'phone'          => $worker?->phone ?? null,
            'role'           => $role?->role_name ?? null,
            'appliedDate'    => $dateStr,
            'status'         => $status,
            'experience'     => $worker?->experience ?? '',
            'skills'         => $worker?->skills ?? '',
            'availability'   => $worker?->availability ?? '',
            'previousEvents' => (int) ($worker?->previous_events_count ?? 0),
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



}
