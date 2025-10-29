<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeesController extends Controller
{

public function index()
{
    $employees = \App\Models\Employee::with('user')
        ->whereHas('user', fn($u) => $u->where('role', 'EMPLOYEE'))
        ->latest()
        ->get()
        ->map(function ($e) {
            return [
                'id'            => $e->employee_id,
                'name'          => $e->user?->name ?? '',
                'email'         => $e->user?->email ?? '',
                'role'          => $e->position ?? '',
                'status'        => strtolower($e->user?->status ?? $e->status ?? 'pending'), // 👈 real DB status
                'eventsManaged' => (int)($e->events_managed ?? 0),
                'joinDate'      => optional($e->hire_date)->toDateString(),
            ];
        });

    return view('Admin.employees', compact('employees'));
}



   public function store(Request $request)
{
    $validated = $request->validate([
        'name'       => 'required|string|max:255',
        'email'      => 'required|email|unique:users,email',
        'password'   => 'required|min:8',
        'position'   => 'nullable|string|max:255',
        'department' => 'nullable|string|max:255',
        'hire_date'  => 'required|date',
        'status'     => 'required|string|in:active,suspended,pending',
        'number'     => 'nullable|string|max:20',
    ]);

    // Create the User first
    $user = \App\Models\User::create([
        'name'     => $validated['name'],
        'email'    => $validated['email'],
        'password' => bcrypt($validated['password']),
        'phone'    => $validated['number'] ?? null,
        'role'     => 'EMPLOYEE',
        'status'   => $validated['status'], // 👈 saves string "active", "suspended", or "pending"
    ]);

    // Then create the Employee
    \App\Models\Employee::create([
        'user_id'    => $user->id,
        'position'   => $validated['position'],
        'department' => $validated['department'],
        'hire_date'  => $validated['hire_date'],
    ]);

    return redirect()->route('employees.index')->with('status', 'Employee added successfully!');
}


    public function search(\Illuminate\Http\Request $request)
{
    $q = trim((string) $request->get('q', ''));

    $query = \App\Models\Employee::with('user')
        ->when($q !== '', function ($qry) use ($q) {
            $qry->whereHas('user', function ($u) use ($q) {
                $u->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            })->orWhere('position', 'like', "%{$q}%")
              ->orWhere('department', 'like', "%{$q}%");
        })
        ->latest();

    $results = $query->get()->map(function ($e) {
        return [
            'id'          => $e->employee_id,
            'name'        => $e->user?->name ?? '',
            'email'       => $e->user?->email ?? '',
            'role'        => $e->position ?? '',
            'department'  => $e->department ?? '',
            'status'      => $e->is_active ? 'active' : 'inactive',
            'eventsManaged'=> (int)($e->events_managed ?? 0),
            'joinDate'    => optional($e->hire_date)->toDateString(),
        ];
    });

    return response()->json($results);
}
    public function json()
    {
        $employees = Employee::with('user')
            ->whereHas('user', fn($u) => $u->where('role', 'EMPLOYEE'))
            ->latest()
            ->get()
            ->map(function ($e) {
                return [
                    'id'            => $e->employee_id,
                    'name'          => $e->user?->name ?? '',
                    'email'         => $e->user?->email ?? '',
                    'role'          => $e->position ?? '',
                    'department'    => $e->department ?? '',
                    'status'        => $e->is_active ? 'active' : 'inactive',
                    'eventsManaged' => (int) ($e->events_managed ?? 0),
                    'joinDate'      => optional($e->hire_date)->toDateString(),
                ];
            });

        return response()->json($employees);
    }


}
