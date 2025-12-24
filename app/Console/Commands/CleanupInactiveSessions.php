<?php

namespace App\Console\Commands;

use App\Models\UserSession;
use Illuminate\Console\Command;

class CleanupInactiveSessions extends Command
{
    protected $signature = 'sessions:cleanup {--days=7 : Days of inactivity before cleanup}';
    protected $description = 'Remove inactive user sessions from the database';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);
        
        $this->info("Cleaning up sessions inactive since {$cutoff->format('Y-m-d H:i:s')}...");
        
        $count = UserSession::where('last_active_at', '<', $cutoff)->count();
        
        if ($count === 0) {
            $this->info('No inactive sessions to clean up.');
            return Command::SUCCESS;
        }
        
        $this->info("Found {$count} inactive session(s).");
        
        UserSession::where('last_active_at', '<', $cutoff)->delete();
        
        $this->info("Deleted {$count} inactive session(s).");
        $this->info("Remaining sessions: " . UserSession::count());
        
        return Command::SUCCESS;
    }
}
