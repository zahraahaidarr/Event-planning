<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeesController extends Controller
{
    public function index()
    {
        // You can eager-load employees and pass to the view if you want to replace the JS demo data.
        // $employees = Employee::with('user')->latest()->get();
        // return view('Admin.employees', compact('employees'));

        return view('Admin.employees');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required','string','max:255'],
            'email'      => ['required','email','max:255', Rule::unique('users','email')],
            'password'   => ['required','string','min:8'],
            'position'   => ['nullable','string','max:255'],
            'department' => ['nullable','string','max:255'],
            'hire_date'  => ['required','date'],
            'is_active'  => ['required','boolean'],
        ]);

        // 1) Create user
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // (Optional) If you keep a role column on users, set it here:
        // $user->role = 'EMPLOYEE';
        // $user->save();

        // 2) Create employee profile (FK -> users.id)
        Employee::create([
            'user_id'    => $user->id,
            'position'   => $data['position'] ?? null,
            'department' => $data['department'] ?? null,
            'hire_date'  => $data['hire_date'],
            'is_active'  => (bool)$data['is_active'],
        ]);

        return redirect()
            ->route('admin.employees.index')
            ->with('ok', 'Employee created successfully.');
    }
}
