<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Employee;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function index()
    {
        // ----- Stats -----
        // volunteers = workers.is_volunteer = 1
        $totalVolunteers = Worker::where('is_volunteer', 1)->count();

        // paid workers = workers.is_volunteer = 0   (adjust if you use another flag)
        $totalPaidWorkers = Worker::where('is_volunteer', 0)->count();

        // all employees (regardless of is_active)
        $totalEmployees = Employee::count();

        // all events
        $totalEvents = Event::count();

        // ----- Recent data for JS (unchanged) -----
        $recentEmployees = Employee::with('user')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->map(function ($e) {
                $u = $e->user;
                $name = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
                $name = $name !== '' ? $name : ($u->email ?? 'Employee');

                $joined = $e->hire_date ?? $e->created_at;

                return [
                    'name'      => $name,
                    'role'      => strtoupper($u->role ?? 'EMPLOYEE'),
                    'joined'    => Carbon::parse($joined)->diffForHumans(),
                    'is_active' => (bool) $e->is_active,
                ];
            });

        $recentEvents = Event::orderByDesc('starts_at')
            ->limit(3)
            ->get()
            ->map(function ($ev) {
                return [
                    'title'     => $ev->title,
                    'location'  => $ev->location ?? 'Unknown location',
                    'starts_at' => $ev->starts_at
                        ? Carbon::parse($ev->starts_at)->format('Y-m-d H:i')
                        : null,
                    'is_done'   => $ev->ends_at
                        ? Carbon::parse($ev->ends_at)->lt(now())
                        : false,
                ];
            });
// ===== Top Workers & Volunteers Rating =====
$topWorkersRating = DB::table('post_event_submissions as pes')
    ->join('workers as w', 'w.worker_id', '=', 'pes.worker_id')
    ->join('users as u', 'u.id', '=', 'w.user_id')
    ->where('pes.status', 'approved')                 // ✅ recommended
    ->whereNotNull('pes.worker_rating')              // ✅ client -> worker
    ->select(
        'w.worker_id',
        'w.user_id',
        'w.is_volunteer',
        DB::raw('AVG(pes.worker_rating) as avg_rating'),
        DB::raw('COUNT(pes.worker_rating) as ratings_count'),
        DB::raw("MAX(TRIM(CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')))) as name")
    )
    ->groupBy('w.worker_id', 'w.user_id', 'w.is_volunteer')
    ->orderByDesc('avg_rating')
    ->orderByDesc('ratings_count')
    ->limit(5)
    ->get();



// ===== Top Clients / Employees Rating =====
// ===== Top Reliable Clients (completed vs cancelled) =====
// ===== Top Reliable Clients (based on workers_reservations COMPLETED) =====
$topClientsReliability = DB::table('events as e')
    ->join('users as u', 'u.id', '=', 'e.created_by')
    ->leftJoin('workers_reservations as wr', 'wr.event_id', '=', 'e.event_id')
    ->where('u.role', 'EMPLOYEE') // clients
    ->select(
        'e.created_by as user_id',
        DB::raw("MAX(TRIM(CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')))) as name"),

        // total events created by this client
        DB::raw('COUNT(DISTINCT e.event_id) as total_events'),

        // cancelled events (if you keep event.status)
        DB::raw("COUNT(DISTINCT CASE WHEN e.status = 'CANCELLED' THEN e.event_id END) as cancelled_events"),

        // completed events = event has at least ONE reservation COMPLETED
        DB::raw("COUNT(DISTINCT CASE WHEN wr.status = 'COMPLETED' THEN e.event_id END) as completed_events")
    )
    ->groupBy('e.created_by')
    ->havingRaw('COUNT(DISTINCT e.event_id) > 0')
    ->orderByRaw("
        (COUNT(DISTINCT CASE WHEN wr.status = 'COMPLETED' THEN e.event_id END) / COUNT(DISTINCT e.event_id)) DESC
    ")
    ->limit(5)
    ->get()
    ->map(function ($row) {
        $total = (int) $row->total_events;
        $completed = (int) $row->completed_events;

        $row->reliability_pct = $total > 0 ? round(($completed / $total) * 100) : 0;
        return $row;
    });


    return view('Admin.dashboard', compact(
    'totalVolunteers',
    'totalPaidWorkers',
    'totalEmployees',
    'totalEvents',
    'recentEmployees',
    'recentEvents',
    'topWorkersRating',
    'topClientsReliability'
));


    }
}
