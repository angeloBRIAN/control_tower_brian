<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Role hierarchy - higher roles include lower permissions
     */
    protected array $roleHierarchy = [
        'admin' => ['admin', 'manager', 'control_tower', 'sparepart', 'sa', 'foreman', 'audit', 'user'],
        'manager' => ['manager', 'control_tower', 'sparepart', 'sa', 'foreman', 'audit', 'user'],
        'control_tower' => ['control_tower', 'sparepart', 'sa', 'foreman', 'user'],
        'sparepart' => ['sparepart', 'user'],
        'sa' => ['sa', 'user'],
        'foreman' => ['foreman', 'user'],
        'audit' => ['audit', 'user'],
        'user' => ['user'],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Allowed roles for this route
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $userRole = $user->role ?? 'user';

        // Check if user's role is in the allowed roles (using hierarchy)
        foreach ($roles as $allowedRole) {
            if ($this->userHasRole($userRole, $allowedRole)) {
                return $next($request);
            }
        }

        // User doesn't have required role
        abort(403, 'You do not have permission to access this page.');
    }

    /**
     * Check if user role includes the required role
     */
    protected function userHasRole(string $userRole, string $requiredRole): bool
    {
        // Direct match
        if ($userRole === $requiredRole) {
            return true;
        }

        // Check hierarchy - if user role includes the required role
        $allowedRoles = $this->roleHierarchy[$userRole] ?? ['user'];
        return in_array($requiredRole, $allowedRoles);
    }
}
