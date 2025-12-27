<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EagerLoadUserRole
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $userWithRole = \App\Models\User::with('role')->find($user->id);
            if ($userWithRole) {
                Auth::setUser($userWithRole);
            }
        }
        return $next($request);
    }
}
