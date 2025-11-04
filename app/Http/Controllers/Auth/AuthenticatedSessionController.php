<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /** GET /login */
    public function create()
    {
        return view('auth.login');
    }

    /** POST /login */
    public function store(Request $request)
    {
        $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
        ]);

        // --- Check if user exists and status is not ACTIVE ---
        $user = User::where('email', $request->email)->first();
        if ($user && ($user->status ?? 'PENDING') !== 'ACTIVE') {
            return back()->withErrors([
                'email' => 'Your account is not active. Please wait for admin approval.',
            ])->onlyInput('email');
        }

        // --- Attempt login only for ACTIVE users ---
        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
            'status'   => 'ACTIVE',
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $role = strtoupper((string)($user->role ?? ''));

        // Redirect by role
        if ($role === 'ADMIN')    { return redirect()->intended(route('admin.dashboard')); }
        if ($role === 'EMPLOYEE') { return redirect()->intended(route('employee.dashboard')); }

        if ($user->worker()->exists()) {
            return redirect()->intended(route('worker.dashboard'));
        }

        // Unknown account type → logout for safety
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => 'Unauthorized role.',
        ]);
    }

    /** POST /logout */
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
