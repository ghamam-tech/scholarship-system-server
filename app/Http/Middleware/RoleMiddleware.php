<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $userRole = $request->user()->role;
        $userRoleValue = is_object($userRole) ? $userRole->value : $userRole;

        $allowedRoles = collect($roles)
            ->flatMap(fn ($value) => preg_split('/[,\|]/', $value))
            ->filter()
            ->map(fn ($value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!in_array($userRoleValue, $allowedRoles, true)) {
            return response()->json(['message' => 'Forbidden. Insufficient permissions.'], 403);
        }

        return $next($request);
    }
}
