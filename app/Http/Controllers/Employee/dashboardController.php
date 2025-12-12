<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $userId = Auth::id();

        $employeeRow = DB::table('employees')->where('user_id', $userId)->first();
        $employeeId  = $employeeRow->employee_id ?? null;

        $createdByIds = $employeeId ? [$userId, $employeeId] : [$userId];

        // 1) Total events created by this employee
        $totalEvents = DB::table('events')
            ->whereIn('created_by', $createdByIds)
            ->count();

        // 2) Completed events (from workers_reservations)
        // COUNT DISTINCT event_id that has COMPLETED reservation
        $completedEvents = DB::table('workers_reservations as wr')
            ->join('events as e', 'e.event_id', '=', 'wr.event_id')
            ->whereIn('e.created_by', $createdByIds)
            ->where('wr.status', 'COMPLETED')
            ->distinct('wr.event_id')
            ->count('wr.event_id');

        // 3) Total Workers (volunteers + paid)
        $totalVolunteersOnly = DB::table('workers')
            ->where('engagement_kind', 'VOLUNTEER')
            ->count();

        $totalPaidWorkersOnly = DB::table('workers')
            ->where('engagement_kind', 'PAID')
            ->count();

        $totalPeople = $totalVolunteersOnly + $totalPaidWorkersOnly;

        // 4) Pending reports for this employee events
        $pendingReports = DB::table('post_event_submissions as s')
            ->join('events as e', 'e.event_id', '=', 's.event_id')
            ->whereIn('e.created_by', $createdByIds)
            ->where('s.status', 'pending')
            ->count();
// --- Upcoming Events list (real data) ---
$upcomingEvents = DB::table('events as e')
    ->leftJoin('work_roles as wr', 'wr.event_id', '=', 'e.event_id')
->leftJoin('workers_reservations as res', function ($join) {
    $join->on('res.event_id', '=', 'e.event_id')
         ->where('res.status', 'RESERVED');
})
    ->whereIn('e.created_by', $createdByIds)
    ->whereNotNull('e.starts_at')
    ->where('e.starts_at', '>', $now)
    ->groupBy('e.event_id', 'e.title', 'e.location', 'e.starts_at', 'e.ends_at', 'e.status')
    ->selectRaw('
        e.event_id,
        e.title,
        e.location,
        e.starts_at,
        e.ends_at,
        e.status,
        COALESCE(SUM(DISTINCT wr.required_spots), 0) as required_spots,
        COUNT(DISTINCT res.reservation_id) as assigned_count
    ')
    ->orderBy('e.starts_at', 'asc')
    ->limit(4)
    ->get();

// Helper: add badge & progress values
$upcomingEvents = $upcomingEvents->map(function ($ev) {
    $total = (int) $ev->required_spots;
    $done  = (int) $ev->assigned_count;

    $pct = ($total > 0) ? (int) round(($done / $total) * 100) : 0;
    if ($pct < 0) $pct = 0;
    if ($pct > 100) $pct = 100;

    // Badge logic (simple & readable)
    if ($total === 0) {
        $badge = ['text' => 'Ready', 'class' => 'badge-success'];
    } elseif ($done >= $total) {
        $badge = ['text' => 'Full', 'class' => 'badge-success'];
    } elseif ($done === 0) {
        $badge = ['text' => 'Urgent', 'class' => 'badge-danger'];
    } else {
        $ratio = $done / $total;
        $badge = ($ratio >= 0.7)
            ? ['text' => 'Ready', 'class' => 'badge-success']
            : ['text' => 'Needs Volunteers', 'class' => 'badge-warning'];
    }

    $ev->progress_pct  = $pct;
    $ev->badge_text    = $badge['text'];
    $ev->badge_class   = $badge['class'];
    $ev->assigned_done = $done;
    $ev->assigned_total= $total;

    return $ev;
});
// ----------------------
// Pending Tasks (derived)
// ----------------------

// 1) Review pending post-event reports (for my events)
$pendingReportsCount = DB::table('post_event_submissions as s')
    ->join('events as e', 'e.event_id', '=', 's.event_id')
    ->whereIn('e.created_by', $createdByIds)
    ->where('s.status', 'pending')
    ->count();

// 2) Approve volunteer applications = PENDING reservations (for my events)
$pendingApplicationsCount = DB::table('workers_reservations as wr')
    ->join('events as e', 'e.event_id', '=', 'wr.event_id')
    ->whereIn('e.created_by', $createdByIds)
    ->where('wr.status', 'PENDING')
    ->count();

// 3) Reminder: events starting in next 24h and not fully staffed (RESERVED only)
$soonStart = $now->copy()->addHours(24);

$eventsNeedReminderCount = DB::table('events as e')
    ->leftJoin('work_roles as r', 'r.event_id', '=', 'e.event_id')
    ->leftJoin('workers_reservations as wr', function ($join) {
        $join->on('wr.event_id', '=', 'e.event_id')
             ->where('wr.status', 'RESERVED'); // ✅ only RESERVED counts
    })
    ->whereIn('e.created_by', $createdByIds)
    ->whereNotNull('e.starts_at')
    ->whereBetween('e.starts_at', [$now, $soonStart])
    ->groupBy('e.event_id')
    ->havingRaw('COUNT(DISTINCT wr.reservation_id) < COALESCE(SUM(DISTINCT r.required_spots), 0)')
    ->pluck('e.event_id')   // ✅ get event ids only
    ->unique()
    ->count();              // ✅ count in PHP (no SQL group-by count issue)




// 5) Respond to messages: unread messages for this employee
// IMPORTANT: adjust column names to your messages table (receiver_id/read_at)
$unreadMessagesCount = DB::table('messages')
    ->where('receiver_id', $userId)
    ->where('is_read', 0)
    ->count();


// Build tasks array (keep your current UI wording)
$tasks = [
    ['text' => "Review {$pendingReportsCount} post-event reports", 'priority' => 'High',   'badgeClass' => 'badge-warning', 'count' => $pendingReportsCount],
    ['text' => "Approve {$pendingApplicationsCount} volunteer applications", 'priority' => 'Medium', 'badgeClass' => 'badge-primary', 'count' => $pendingApplicationsCount],
    ['text' => "Send reminder for {$eventsNeedReminderCount} upcoming events", 'priority' => 'High',  'badgeClass' => 'badge-warning', 'count' => $eventsNeedReminderCount],
    ['text' => "Respond to {$unreadMessagesCount} volunteer messages", 'priority' => 'Medium', 'badgeClass' => 'badge-primary', 'count' => $unreadMessagesCount],
];

// Optional: show only tasks that actually have something to do
$tasks = array_values(array_filter($tasks, fn($t) => ($t['count'] ?? 0) > 0));

$tasksCount = count($tasks);
$recentActivity = [];

// A) Event completed (from events table)
$lastCompletedEvent = DB::table('events as e')
    ->whereIn('e.created_by', $createdByIds)
    ->where('e.status', 'COMPLETED')
    ->orderByDesc('e.updated_at')
    ->select('e.event_id', 'e.title', 'e.updated_at')
    ->first();

if ($lastCompletedEvent) {
    // count completed reservations for that event (participants)
    $completedCount = DB::table('workers_reservations')
        ->where('event_id', $lastCompletedEvent->event_id)
        ->where('status', 'COMPLETED')
        ->count();

    $recentActivity[] = [
        'icon'  => 'success', // maps to your status-icon success
        'title' => "{$lastCompletedEvent->title} - Event Completed",
        'meta'  => "{$completedCount} volunteers participated",
        'time'  => Carbon::parse($lastCompletedEvent->updated_at)->diffForHumans(),
    ];
}

// B) New volunteer application received (latest PENDING reservation)
$lastApp = DB::table('workers_reservations as wr')
    ->join('events as e', 'e.event_id', '=', 'wr.event_id')
    ->join('workers as w', 'w.worker_id', '=', 'wr.worker_id')
    ->leftJoin('users as u', 'u.id', '=', 'w.user_id')
    ->whereIn('e.created_by', $createdByIds)
    ->whereIn('wr.status', ['PENDING', 'RESERVED'])   // ✅ include RESERVED too
    ->orderByDesc(DB::raw('COALESCE(wr.reserved_at, wr.created_at, wr.updated_at)'))
    ->select(
        'wr.status',
        'wr.reserved_at',
        'wr.created_at',
        'wr.updated_at',
        'e.title as event_title',
        'u.first_name',
        'u.last_name'
    )
    ->first();

if ($lastApp) {
    $name = trim(($lastApp->first_name ?? '') . ' ' . ($lastApp->last_name ?? ''));
    if ($name === '') $name = 'A volunteer';

    $when = $lastApp->reserved_at ?? $lastApp->created_at ?? $lastApp->updated_at;

    $recentActivity[] = [
        'icon'  => 'primary',
        'title' => "New volunteer application received",
        'meta'  => "{$name} applied to '{$lastApp->event_title}'",
        'time'  => \Carbon\Carbon::parse($when)->diffForHumans(),
    ];
}



// C) Post-event report submitted (latest submission)
$lastReport = DB::table('post_event_submissions as s')
    ->join('events as e', 'e.event_id', '=', 's.event_id')
    ->whereIn('e.created_by', $createdByIds)
    ->orderByDesc('s.created_at')
    ->select('s.created_at', 's.role_slug', 'e.title as event_title')
    ->first();

if ($lastReport) {
    $role = $lastReport->role_slug ? ucfirst($lastReport->role_slug) : 'worker';

    $recentActivity[] = [
        'icon'  => 'warning',
        'title' => "Post-event report submitted",
        'meta'  => "{$role} submitted a report for {$lastReport->event_title}",
        'time'  => Carbon::parse($lastReport->created_at)->diffForHumans(),
    ];
}

// keep only 3 items
$recentActivity = array_slice($recentActivity, 0, 3);
        return view('Employee.dashboard', [
            'totalEvents'          => $totalEvents,
            'completedEvents'      => $completedEvents,
            'totalPeople'          => $totalPeople,
            'totalVolunteersOnly'  => $totalVolunteersOnly,
            'totalPaidWorkersOnly' => $totalPaidWorkersOnly,
            'pendingReports'       => $pendingReports,
            'upcomingEvents' => $upcomingEvents,
            'tasks' => $tasks,
            'tasksCount' => $tasksCount,
            'recentActivity' => $recentActivity,

            // keep if blade still references it somewhere
            'activeEvents'         => 0,
            
        ]);
    }
}
