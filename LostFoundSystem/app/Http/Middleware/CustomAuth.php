<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * CustomAuth middleware — protects routes that require authentication.
 * Uses the custom session-based auth (no third-party packages).
 */
class CustomAuth
{
    public function handle(Request $request, Closure $next, ?string $role = null)
    {
        $userId = $request->session()->get('auth_user_id');

        if (!$userId) {
            return redirect('/login')->with('error', 'Please log in to access that page.');
        }

        // If a specific role is required, check it
        if ($role) {
            $userRole = $request->session()->get('auth_user_role');

            if ($userRole !== $role && $userRole !== 'admin') {
                abort(403, 'You do not have permission to access this page.');
            }
        }

        return $next($request);
    }
}
