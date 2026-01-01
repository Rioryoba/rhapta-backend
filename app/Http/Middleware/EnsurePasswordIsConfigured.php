yes<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePasswordIsConfigured
{
    /**
     * Handle an incoming request.
     * Only allow access if password_configured is true, except for setPassword endpoint.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && !$user->password_configured) {
            // Allow only the set-password endpoint
            if ($request->is('api/users/set-password')) {
                return $next($request);
            }
            return response()->json([
                'message' => 'You must set your password before accessing the system.'
            ], 403);
        }
        return $next($request);
    }
}
