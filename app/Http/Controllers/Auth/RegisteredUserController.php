<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Worker;
use App\Models\RoleType;
use App\Models\Employee;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    /** GET /register */
    public function create(): View
    {
        $roleTypes = RoleType::orderBy('name')->get(['role_type_id','name']);
        return view('auth.register', compact('roleTypes'));
    }

    /** POST /register */
    public function store(Request $request): RedirectResponse
    {
        $type = $request->input('account_type', 'worker');

        /* ======================= EMPLOYEE FLOW ======================= */
        if ($type === 'employee') {
            $validated = $request->validate([
                'e_first_name'    => ['required','string','max:255'],
                'e_last_name'     => ['required','string','max:255'],
                'e_email'         => ['required','string','email','max:255','unique:users,email'],
                'e_phone'         => ['required','string','max:30'],
                'e_date_of_birth' => ['required','date','before:today'],
                'e_password'      => ['required','confirmed', Password::min(6)],
            ]);

            // User: ACTIVE + role = employee
            $user = User::create([
                'first_name'    => $validated['e_first_name'],
                'last_name'     => $validated['e_last_name'],
                'email'         => $validated['e_email'],
                'phone'         => $validated['e_phone'],
                'date_of_birth' => $validated['e_date_of_birth'],
                'status'        => 'ACTIVE',
                'role'          => 'employee',
                'password'      => Hash::make($validated['e_password']),
            ]);

            // Minimal Employee profile
            if (class_exists(\App\Models\Employee::class)) {
                Employee::create([
                    'user_id'   => $user->id,
                    'status'    => 'ACTIVE',
                    'hire_date' => now()->toDateString(),
                ]);
            }

            event(new Registered($user));

            return redirect()
                ->route('login')
                ->with('status', 'Employee account created. You can sign in now.');
        }

        /* ======================= WORKER FLOW ======================= */
        $validated = $request->validate([
            'first_name'    => ['required','string','max:255'],
            'last_name'     => ['required','string','max:255'],
            'email'         => ['required','string','email','max:255','unique:users,email'],
            'phone'         => ['nullable','string','max:30'],
            'city'          => ['nullable','string','max:100'],
            'role_type_id'  => ['required','integer','exists:role_types,role_type_id'],
            'certificate'   => ['required','file','mimes:pdf,jpg,jpeg,png','max:4096'],
            'date_of_birth' => ['required','date','before:today'],
            'password'      => ['required','confirmed', Password::min(6)],
            'terms'         => ['accepted'],
        ]);

        // User: PENDING + role = worker
        $user = User::create([
            'first_name'    => $validated['first_name'],
            'last_name'     => $validated['last_name'],
            'email'         => $validated['email'],
            'phone'         => $validated['phone'] ?? null,
            'date_of_birth' => $validated['date_of_birth'],
            'status'        => 'PENDING',
            'role'          => 'worker',
            'password'      => Hash::make($validated['password']),
        ]);

        $certificatePath = $request->file('certificate')->store('certificates', 'public');

        Worker::create([
            'user_id'             => $user->id,
            'role_type_id'        => (int) $validated['role_type_id'],
            'engagement_kind'     => 'VOLUNTEER',
            'is_volunteer'        => true,
            'location'            => $validated['city'] ?? null,
            'certificate_path'    => $certificatePath,
            'total_hours'         => 0,
            'verification_status' => 'PENDING',
            'joined_at'           => now()->toDateString(),
        ]);

        event(new Registered($user));

        return redirect()
            ->route('login')
            ->with('status', 'Account created. Your account is not active. Please wait for admin approval.');
    }
}
