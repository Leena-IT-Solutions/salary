<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $role = $user->role;
        $path = $request->getPathInfo();

        // Administrator has access to everything
        if ($role === 'Administrator') {
            return $next($request);
        }

        // Common routes accessible by everyone
        $commonPaths = [
            '/logout',
            '/employee/dashboard',
            '/employee/profile',
            '/employee/payslips',
            '/employee/attendance',
            '/employee/holidays',
            '/employee/requests',
            '/approvals/leave',
            '/approvals/time_update',
            '/approvals/shortleave',
            '/approvals/overtime',
            '/',
        ];

        foreach ($commonPaths as $commonPath) {
            if (str_starts_with($path, $commonPath)) {
                return $next($request);
            }
        }

        // Time Office specific access
        if ($role === 'Time Office') {
            $allowedTimeOfficePaths = [
                '/',
                '/calender',
                '/employee_shift',
                '/attendance',
                '/approvals/pending',
                '/approvals/leave',
                '/approvals/time_update',
                '/approvals/on_duty',
                '/approvals/shortleave',
                '/approvals/overtime',
            ];

            foreach ($allowedTimeOfficePaths as $allowedPath) {
                // Exact match or sub-path match
                if ($path === $allowedPath || str_starts_with($path, $allowedPath . '/')) {
                    return $next($request);
                }
            }
        }

        // If Employee (or Time Office trying to access unauthorized areas)
        return response()->view('errors.no_permission', [], 403);
    }
}
