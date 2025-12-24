<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use App\Models\BackupSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SessionController extends Controller
{
    /**
     * Display all user sessions with stats
     */
    public function index(Request $request)
    {
        $query = UserSession::with('user')->orderBy('last_active_at', 'desc');
        
        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by device type
        if ($request->filled('device')) {
            $query->where('device_type', $request->device);
        }
        
        $sessions = $query->paginate(20);
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        
        // Session stats
        $stats = [
            'total_sessions' => UserSession::count(),
            'online_now' => UserSession::where('last_active_at', '>=', now()->subMinutes(5))->count(),
            'today_logins' => UserSession::whereDate('created_at', today())->count(),
            'unique_users_today' => UserSession::whereDate('last_active_at', today())->distinct('user_id')->count('user_id'),
            'devices' => [
                'desktop' => UserSession::where('device_type', 'desktop')->count(),
                'mobile' => UserSession::where('device_type', 'mobile')->count(),
                'tablet' => UserSession::where('device_type', 'tablet')->count(),
            ],
        ];

        // Get settings
        $schedule = BackupSchedule::first() ?? new BackupSchedule([
            'session_cleanup_enabled' => true,
            'session_cleanup_days' => 7,
        ]);
        
        return view('admin.sessions.index', compact('sessions', 'users', 'stats', 'schedule'));
    }

    /**
     * Update session cleanup settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'session_cleanup_enabled' => 'nullable|boolean',
            'session_cleanup_days' => 'required|integer|min:1|max:365',
        ]);

        BackupSchedule::updateOrCreate(
            ['id' => 1],
            [
                'session_cleanup_enabled' => $request->boolean('session_cleanup_enabled'),
                'session_cleanup_days' => $request->input('session_cleanup_days'),
            ]
        );

        return back()->with('success', 'Session cleanup settings updated.');
    }

    /**
     * Manually run session cleanup
     */
    public function cleanup(Request $request)
    {
        $schedule = BackupSchedule::first();
        $days = $schedule->session_cleanup_days ?? 7;

        try {
            Artisan::call('sessions:cleanup', ['--days' => $days]);
            $output = trim(Artisan::output());
            return back()->with('success', 'Cleanup completed. ' . $output);
        } catch (\Exception $e) {
            return back()->with('error', 'Cleanup failed: ' . $e->getMessage());
        }
    }

    /**
     * Terminate a specific session
     */
    public function terminate(UserSession $session)
    {
        $userName = $session->user->name ?? 'Unknown';
        $session->delete();
        
        return back()->with('success', "Session for {$userName} has been terminated.");
    }

    /**
     * Terminate all sessions for a specific user
     */
    public function terminateUser(User $user)
    {
        $count = UserSession::where('user_id', $user->id)->delete();
        
        return back()->with('success', "All {$count} session(s) for {$user->name} have been terminated.");
    }

    /**
     * Terminate all sessions except current user's
     */
    public function terminateAllOthers()
    {
        $count = UserSession::where('user_id', '!=', auth()->id())->delete();
        
        return back()->with('success', "{$count} session(s) have been terminated.");
    }
}
