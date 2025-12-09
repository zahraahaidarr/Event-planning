<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\PostEventSubmission;
use App\Models\WorkRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PostEventSubmissionFile;
use App\Models\PostEventCivilCase;
use App\Models\RoleType;
use App\Services\Notify;
use Illuminate\Support\Facades\DB;



class PostEventReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = PostEventSubmission::with([
    'event',
    'worker',
    // remove this line if you don't actually have worker->user relation
    // 'worker.user',
    'workRole',
    'files',
    'civilCases',
]);


// ---------- Filters (GET) ----------
if ($request->filled('event_id')) {
    $query->where('event_id', $request->event_id);
}

if ($request->filled('role_slug')) {
    $roleTypeId = (int) $request->role_slug;

    // Filter submissions whose workRole has this role_type_id
    $query->whereHas('workRole', function ($q) use ($roleTypeId) {
        $q->where('role_type_id', $roleTypeId);
    });
}

if ($request->filled('status')) {
    $query->where('status', $request->status);
}

if ($request->filled('search')) {
    $search = $request->search;

    $query->whereHas('worker.user', function ($q) use ($search) {
        $q->where(function ($inner) use ($search) {
            $inner->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
        });
    });
}




        $submissions = $query
            ->orderByDesc('submitted_at')
            ->paginate(15)
            ->withQueryString();

        // ---------- Stats ----------
        $stats = [
            'pending'  => PostEventSubmission::where('status', 'pending')->count(),
            'approved' => PostEventSubmission::where('status', 'approved')->count(),
            'rejected' => PostEventSubmission::where('status', 'rejected')->count(),
            'total'    => PostEventSubmission::count(),
        ];

        // filters data
        // filters data
$filterEvents = Event::whereIn('event_id', function ($q) {
    $q->select('event_id')->from('post_event_submissions');
})
->orderBy('starts_at', 'desc')
->get();



    

$filterRoles = RoleType::whereIn('role_type_id', function ($q) {
    $q->select('role_type_id')
      ->from('work_roles')
      ->whereIn('role_id', function ($sq) {
          $sq->select('work_role_id')->from('post_event_submissions');
      });
})->orderBy('name')->get();




        return view('employee.post-event-reports', [
            'submissions'  => $submissions,
            'stats'        => $stats,
            'filterEvents' => $filterEvents,
            'filterRoles'  => $filterRoles,
            'filters'      => [
                'event_id'  => $request->event_id,
                'role_slug' => $request->role_slug,
                'status'    => $request->status,
                'search'    => $request->search,
            ],
        ]);
    }

 
public function approve(PostEventSubmission $submission, Request $request)
{
    // get rating from form
    $rating = $request->integer('worker_rating');
    if ($rating < 1 || $rating > 5) {
        $rating = null;
    }

    $submission->update([
        'status'        => 'approved',
        'reviewed_at'   => now(),
        'reviewed_by'   => Auth::id(),
        'review_notes'  => $request->input('review_notes'),
        'worker_rating' => $rating,
    ]);

    $submission->loadMissing('event', 'worker');

    $workerUserId = $submission->worker->user_id ?? null;
    $eventTitle   = optional($submission->event)->title ?? 'the event';

    if ($workerUserId) {
        Notify::to(
            $workerUserId,
            'Post-event report approved',
            "Your {$submission->role_label} report for '{$eventTitle}' has been approved.",
            'POST_EVENT_APPROVED'
        );
    }

    return back()->with('success', 'Report approved successfully.');
}

public function reject(PostEventSubmission $submission, Request $request)
{
    $request->validate([
        'reason' => 'required|string|max:1000',
    ]);

    $rating = $request->integer('worker_rating');
    if ($rating < 1 || $rating > 5) {
        $rating = null;
    }

    $submission->update([
        'status'        => 'rejected',
        'reviewed_at'   => now(),
        'reviewed_by'   => Auth::id(),
        'review_notes'  => $request->reason,
        'worker_rating' => $rating,
    ]);

    $submission->loadMissing('event', 'worker');

    $workerUserId = $submission->worker->user_id ?? null;
    $eventTitle   = optional($submission->event)->title ?? 'the event';

    if ($workerUserId) {
        Notify::to(
            $workerUserId,
            'Post-event report rejected',
            "Your {$submission->role_label} report for '{$eventTitle}' was rejected. Reason: {$request->reason}",
            'POST_EVENT_REJECTED'
        );
    }

    return back()->with('success', 'Report rejected and notes saved.');
}

}
