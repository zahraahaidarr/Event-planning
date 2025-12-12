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
    ->whereNotNull('pes.owner_rating')
    ->select(
        'w.worker_id',
        'w.user_id',
        'w.is_volunteer',
        DB::raw('AVG(pes.owner_rating) as avg_rating'),
        DB::raw('COUNT(pes.owner_rating) as ratings_count'),
        DB::raw("MAX(TRIM(CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')))) as name")
    )
    ->groupBy('w.worker_id', 'w.user_id', 'w.is_volunteer')
    ->orderByDesc('avg_rating')
    ->orderByDesc('ratings_count') // tie breaker: more ratings first
    ->limit(5)
    ->get();


// ===== Top Clients / Employees Rating =====
$topClientsRating = DB::table('post_event_submissions as pes')
    ->join('events as e', 'e.event_id', '=', 'pes.event_id')
    ->join('users as u', 'u.id', '=', 'e.created_by')   // ✅ creator user
    ->whereNotNull('pes.owner_rating')
    // Optional: if you ONLY want employees as clients:
     ->where('u.role', 'EMPLOYEE')
    ->select(
        'e.created_by as user_id',
        DB::raw('AVG(pes.owner_rating) as avg_rating'),
        DB::raw('COUNT(pes.owner_rating) as ratings_count'),
        DB::raw("MAX(TRIM(CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')))) as name")
    )
    ->groupBy('e.created_by')
    ->orderByDesc('avg_rating')
    ->orderByDesc('ratings_count')
    ->limit(5)
    ->get();





return view('Admin.dashboard', compact(
    'totalVolunteers',
    'totalPaidWorkers',
    'totalEmployees',
    'totalEvents',
    'recentEmployees',
    'recentEvents',
    'topWorkersRating',
    'topClientsRating'
));


    }
}
