<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\JobActivity;
use Illuminate\Console\Command;

class RecoverStaleJobStatus extends Command
{
    protected $signature = 'jobs:recover-stale-status {--dry-run : Run without making changes}';
    protected $description = 'Recover original work status for stale jobs that were reset to default';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $jobs = Job::where('is_stale', true)
            ->where('work_status', '1. Belum diproses (Tunggu Antrian)')
            ->get();
            
        $this->info("Found {$jobs->count()} stale jobs with default status.");
        
        $recoveredCount = 0;
        
        foreach ($jobs as $job) {
            // Find the last status change activity
            $lastStatusChange = JobActivity::where('job_id', $job->id)
                ->where('action', 'work_status_changed')
                ->orderBy('created_at', 'desc')
                ->first();
                
            $originalStatus = null;
            
            if ($lastStatusChange) {
                // Try to get status from 'changes' column
                if (isset($lastStatusChange->changes['new'])) {
                    $originalStatus = $lastStatusChange->changes['new'];
                }
                // Fallback: Parse description if changes column is empty/null
                elseif (preg_match("/to '([^']+)'/", $lastStatusChange->description, $matches)) {
                    $originalStatus = $matches[1];
                }
            } else {
                // If no status change, check creation (default status might have been valid)
                // But usually creation defaults to 'Belum diproses', so maybe no need to check
            }
            
            // If we found a status and it's not 'needs_attention' (just in case), restore it
            if ($originalStatus && $originalStatus !== 'needs_attention' && $originalStatus !== '1. Belum diproses (Tunggu Antrian)') {
                if ($dryRun) {
                    $this->line("Job {$job->job_number}: Would restore status to '{$originalStatus}'");
                } else {
                    $job->update([
                        'work_status' => $originalStatus,
                        // Don't touch is_stale, keep it true
                    ]);
                    $this->info("Job {$job->job_number}: Restored status to '{$originalStatus}'");
                }
                $recoveredCount++;
            }
        }
        
        $this->info("Recovery complete. Recovered {$recoveredCount} jobs.");
        return 0;
    }
}
