<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Worker;
use App\Models\RoleType;
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
        // pass roles to the view
        $roleTypes = RoleType::orderBy('name')->get(['role_type_id','name']);
        return view('auth.register', compact('roleTypes'));
    }

    /** POST /register */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'     => ['required','string','max:255'],
            'last_name'      => ['required','string','max:255'],
            'email'          => ['required','string','email','max:255','unique:users,email'],
            'phone'          => ['nullable','string','max:30'],
            'city'           => ['nullable','string','max:100'],

            // take the role from the dropdown bound to role_types table
            'role_type_id'   => ['required','integer','exists:role_types,role_type_id'],

            'certificate'    => ['required','file','mimes:pdf,jpg,jpeg,png','max:4096'],
            'password'       => ['required','confirmed', Password::min(6)],
            'terms'          => ['accepted'],
        ]);

        $roleTypeId = (int) $validated['role_type_id'];

        // Create user with PENDING status
        $user = User::create([
            'name'     => trim($validated['first_name'].' '.$validated['last_name']),
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'status'   => 'PENDING',
            'password' => Hash::make($validated['password']),
        ]);

        // Store certificate
        $certificatePath = $request->file('certificate')->store('certificates', 'public');

        // Create worker profile (role lives here)
        Worker::create([
            'user_id'             => $user->id,
            'role_type_id'        => $roleTypeId,                 // <-- use resolved id
            'engagement_kind'     => 'VOLUNTEER',
            'is_volunteer'        => true,
            'location'            => $validated['city'] ?? null,
            'certificate_path'    => $certificatePath,
            'total_hours'         => 0,
            'verification_status' => 'PENDING',
            'approval_status'     => 'PENDING',
            'joined_at'           => now()->toDateString(),
        ]);

        event(new Registered($user));

        return redirect()
            ->route('login')
            ->with('status', 'Account created. Your account is not active. Please wait for admin approval.');
    }
}
