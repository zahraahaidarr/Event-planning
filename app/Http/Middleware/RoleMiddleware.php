<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        $allowedRoles = [];

        foreach ($roles as $role) {
            // Support both "ADMIN|EMPLOYEE" and "ADMIN,EMPLOYEE"
            $parts = preg_split('/[\|,]/', $role);
            $allowedRoles = array_merge($allowedRoles, $parts);
        }

        // Remove duplicates and trim whitespace
        $allowedRoles = array_unique(array_map('trim', $allowedRoles));

        if (! in_array($user->role, $allowedRoles, true)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
