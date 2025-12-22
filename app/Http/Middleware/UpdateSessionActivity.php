<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateSessionActivity
{
    /**
     * Update the user session's last_active_at timestamp on each request.
     * Also validates that the session still exists (for remote logout support).
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $sessionId = session()->getId();
            $userId = Auth::id();
            
            // Check if session was terminated remotely (via Session Manager)
            $sessionExists = UserSession::where('session_id', $sessionId)
                ->where('user_id', $userId)
                ->exists();
            
            if (!$sessionExists) {
                // Session was terminated - log the user out
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('warning', 'Your session was terminated from another device.');
            }
            
            // Update session activity (throttled to once per minute to reduce DB writes)
            $cacheKey = 'session_activity_' . $sessionId;
            
            if (!cache()->has($cacheKey)) {
                UserSession::where('session_id', $sessionId)
                    ->where('user_id', $userId)
                    ->update(['last_active_at' => now()]);
                    
                // Cache for 1 minute to avoid updating on every request
                cache()->put($cacheKey, true, 60);
            }
        }

        return $next($request);
    }
}
