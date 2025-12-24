<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDocTypePermission
{
    /**
     * Handle an incoming request.
     * 
     * Usage: middleware('doctype:Job,read') or middleware('doctype:Job,write')
     * 
     * @param string $doctype The doctype to check (Job, Vehicle, Customer, etc.)
     * @param string $action The action to check (read, write, create, delete, export)
     */
    public function handle(Request $request, Closure $next, string $doctype, string $action = 'read'): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            abort(401, 'Unauthorized');
        }
        
        // Admin always has access
        if ($user->role === 'admin' || $user->roles()->where('slug', 'administrator')->exists()) {
            return $next($request);
        }
        
        // Check permission using the User model's canDo method
        if (!$user->canDo($doctype, $action)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Permission denied',
                    'message' => "You don't have permission to {$action} {$doctype}."
                ], 403);
            }
            
            abort(403, "You don't have permission to {$action} {$doctype}.");
        }
        
        return $next($request);
    }
}
