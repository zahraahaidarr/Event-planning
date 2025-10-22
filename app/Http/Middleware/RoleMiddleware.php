<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    // Usage: ->middleware('role:ADMIN') or role:ADMIN,EMPLOYEE
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
