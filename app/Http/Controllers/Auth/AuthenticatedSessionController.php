<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // redirect by role
        switch ($user->role) {
            case 'ADMIN':
                return redirect()->route('admin.dashboard');
            case 'EMPLOYEE':
                return redirect()->route('employee.dashboard');
            case 'WORKER':
                return redirect()->route('worker.dashboard');
            default:
                // unknown role -> logout for safety
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors([
                    'email' => 'Unauthorized role.',
                ]);
        }
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
