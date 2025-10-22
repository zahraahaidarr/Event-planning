<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Worker;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /** Show the registration page (GET /register) */
    public function create(): View
    {
        return view('auth.register');
    }

    /** Handle registration (POST /register) */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'     => ['required','string','max:255'],
            'last_name'      => ['required','string','max:255'],
            'email'          => ['required','string','email','max:255','unique:users,email'],
            'phone'          => ['nullable','string','max:30'],
            'city'           => ['nullable','string','max:100'],
            'preferred_role' => ['nullable','string','max:100'],
            'certificate'    => ['required','file','mimes:pdf,jpg,jpeg,png','max:4096'],
            'password'       => ['required','confirmed', Rules\Password::defaults()],
            'terms'          => ['accepted'],
        ]);

        $user = User::create([
            'name'     => trim($validated['first_name'].' '.$validated['last_name']),
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'role'     => 'WORKER',
            'status'   => 'PENDING',
            'password' => Hash::make($validated['password']),
        ]);

        $certificatePath = $request->file('certificate')->store('certificates', 'public');

        Worker::create([
            'user_id'             => $user->id,
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
        ->with('status', 'Account created successfully! Please log in to continue.');
    }
}
