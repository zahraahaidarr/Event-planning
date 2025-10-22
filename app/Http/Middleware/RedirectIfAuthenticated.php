<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * If the user is already authenticated and tries to access guest routes
     * (like /login or /register), send them to the correct dashboard by role.
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::user();

                return match ($user->role) {
                    'ADMIN'    => redirect()->route('admin.dashboard'),
                    'EMPLOYEE' => redirect()->route('employee.dashboard'),
                    'WORKER'   => redirect()->route('worker.dashboard'),
                    default    => redirect()->route('login'),
                };
            }
        }

        return $next($request);
    }
}
