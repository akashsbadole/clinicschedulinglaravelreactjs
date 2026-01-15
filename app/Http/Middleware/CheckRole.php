<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        \Log::info('CheckRole middleware', [
            'path' => $request->path(),
            'auth_check' => auth()->check(),
            'user_id' => auth()->check() ? auth()->user()->id : 'null',
            'user_role' => auth()->check() ? auth()->user()->role : 'null',
            'bearer_token' => $request->bearerToken(),
            'expected_roles' => $roles,
        ]);

        if (!auth()->check()) {
            \Log::warning('Authentication failed in CheckRole middleware');
            // For API requests, return JSON response
            if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
                return response()->json(['message' => 'Unauthorized. Please login first.'], 401);
            }
            return redirect()->route('login');
        }

        $user = auth()->user();

        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }

        // For API requests, return JSON response
        if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
            return response()->json(['message' => 'Forbidden. You do not have the required role.'], 403);
        }

        abort(403, 'Unauthorized action.');
    }
}