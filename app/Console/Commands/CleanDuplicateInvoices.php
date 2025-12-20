<?php

namespace App\Console\Commands;

use App\Models\JobInvoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateInvoices extends Command
{
    protected $signature = 'invoices:clean-duplicates {--dry-run : Show what would be deleted without deleting}';
    protected $description = 'Remove duplicate JobInvoice records (same job_id + invoice_number + invoice_date)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info($dryRun ? 'DRY RUN - No records will be deleted' : 'Cleaning duplicate invoices...');

        // Find duplicates: keep the first (oldest) record, delete the rest
        $duplicates = DB::table('job_invoices')
            ->select('job_id', 'invoice_number', 'invoice_date', DB::raw('COUNT(*) as count'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('job_id', 'invoice_number', 'invoice_date')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate invoices found!');
            return 0;
        }

        $this->info("Found {$duplicates->count()} invoice groups with duplicates");
        
        $totalDeleted = 0;
        
        foreach ($duplicates as $dup) {
            // Get all IDs for this duplicate group
            $allIds = JobInvoice::where('job_id', $dup->job_id)
                ->where('invoice_number', $dup->invoice_number)
                ->where('invoice_date', $dup->invoice_date)
                ->pluck('id')
                ->toArray();
            
            // Remove the one we want to keep
            $toDelete = array_filter($allIds, fn($id) => $id != $dup->keep_id);
            
            if ($dryRun) {
                $this->line("Would delete " . count($toDelete) . " duplicates for Invoice #{$dup->invoice_number} (Job ID: {$dup->job_id})");
            } else {
                JobInvoice::whereIn('id', $toDelete)->delete();
                $this->line("Deleted " . count($toDelete) . " duplicates for Invoice #{$dup->invoice_number} (Job ID: {$dup->job_id})");
            }
            
            $totalDeleted += count($toDelete);
        }

        $this->newLine();
        $this->info($dryRun 
            ? "Would delete {$totalDeleted} duplicate invoice records"
            : "Deleted {$totalDeleted} duplicate invoice records");

        return 0;
    }
}
