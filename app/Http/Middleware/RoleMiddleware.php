<?php
namespace App\Http\Middleware;

use Illuminate\Support\Facades\Log;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|string[]  $roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth()->user();
        Log::debug('RoleMiddleware: Checking user and role', [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_role' => $user?->role?->name,
            'allowed_roles' => $roles,
            'route' => $request->path(),
            'token_present' => $request->bearerToken() ? true : false,
        ]);
        if (!$user) {
            Log::warning('RoleMiddleware: No authenticated user', [
                'route' => $request->path(),
                'token_present' => $request->bearerToken() ? true : false,
            ]);
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        // Support both array and comma-separated string
        $allowedRoles = is_array($roles) ? $roles : explode(',', $roles);
        $roleName = $user->role?->name;
        if (!$roleName || !in_array($roleName, $allowedRoles)) {
            Log::warning('RoleMiddleware: Forbidden - user role not allowed', [
                'user_id' => $user->id,
                'user_role' => $roleName,
                'allowed_roles' => $allowedRoles,
                'route' => $request->path(),
            ]);
            return response()->json(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
