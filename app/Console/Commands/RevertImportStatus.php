<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AuditLog;
use App\Models\Job;
use Carbon\Carbon;

class RevertImportStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:revert-import-status 
                            {--hours= : How many hours back to look} 
                            {--date= : Specific date to target (YYYY-MM-DD)} 
                            {--dry-run : Only show what would be changed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revert job work statuses that were reset to "belum_diproses" or other values by mistake in recent imports.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        $date = $this->option('date');
        $isDryRun = $this->option('dry-run');
        
        if (!$hours && !$date) {
            $this->error("Please specify either --hours or --date.");
            return;
        }
        
        $query = AuditLog::where('auditable_type', Job::class)
            ->where('action', 'updated')
            ->whereJsonContains('new_values->work_status', 'belum_diproses')
            ->orderBy('created_at', 'desc');

        if ($date) {
            $this->info("Scanning Audit Logs for date: {$date}...");
            $query->whereDate('created_at', $date);
        } else {
            $this->info("Scanning Audit Logs for the last {$hours} hours...");
            $since = Carbon::now()->subHours($hours);
            $query->where('created_at', '>=', $since);
        }
        
        $logs = $query->get();

        $count = 0;
        
        foreach ($logs as $log) {
            $newStatus = $log->new_values['work_status'] ?? null;
            $oldStatus = $log->old_values['work_status'] ?? null;
            
            // Only revert if:
            // 1. New Status is 'belum_diproses' (the accidental reset)
            // 2. Old Status was NOT 'belum_diproses' (it had a value)
            // 3. Old Status wasn't null
            if ($newStatus === 'belum_diproses' && $oldStatus && $oldStatus !== 'belum_diproses') {
                
                $job = Job::find($log->auditable_id);
                
                if (!$job) {
                    $this->warn("Job ID {$log->auditable_id} not found. Skipping.");
                    continue;
                }
                
                // Safety check: Is the CURRENT status still 'belum_diproses'? 
                // If user manually fixed it already, don't overwrite their fix!
                if ($job->work_status !== 'belum_diproses') {
                    $this->info("Job {$job->job_number}: Status is currently '{$job->work_status}', not 'belum_diproses'. Skipping (already fixed?).");
                    continue;
                }
                
                $this->line("Job {$job->job_number}: Reverting '{$newStatus}' -> '{$oldStatus}'");
                
                if (!$isDryRun) {
                    $job->update(['work_status' => $oldStatus]);
                }
                $count++;
            }
        }
        
        if ($count === 0) {
            $this->info("No matching status resets found.");
        } else {
            $action = $isDryRun ? "Found" : "Reverted";
            $this->info("$action {$count} job statuses.");
        }
    }
}
