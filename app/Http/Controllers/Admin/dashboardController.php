<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\Employee;
use App\Models\Event;
use Carbon\Carbon;

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

        return view('Admin.dashboard', compact(
            'totalVolunteers',
            'totalPaidWorkers',
            'totalEmployees',
            'totalEvents',
            'recentEmployees',
            'recentEvents'
        ));
    }
}
