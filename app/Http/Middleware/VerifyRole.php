<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Role(s) allowed to access the route
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Admin finance has access to everything
        if ($user->hasRole(User::ROLE_ADMIN_FINANCE)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            return response()->json([
                'message' => 'Forbidden. You do not have the required role.',
                'required_roles' => $roles,
            ], 403);
        }

        return $next($request);
    }
}
