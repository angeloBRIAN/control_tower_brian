<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixCrossFranchiseDupes extends Command
{
    protected $signature = 'wip:fix-cross-franchise
        {--dry-run : Preview changes without modifying anything}
        {--keep=latest : Which record to keep: latest|pc|cv}
        {--import-id=274 : Delete records from this import (wrong franchise)}';

    protected $description = 'Fix silent cross-franchise duplicates (same WIP+Plate in both PC and CV)';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run') !== false;
        $keepStrategy = $this->option('keep');
        $deleteImportId = $this->option('import-id');

        if ($isDryRun) {
            $this->warn('═══ DRY RUN MODE ═══ No changes will be made');
            $this->newLine();
        }

        // Find all WIPs that exist in BOTH PC and CV with same plate
        $dupes = DB::table('jobs as j1')
            ->join('jobs as j2', function ($join) {
                $join->on('j1.job_number', '=', 'j2.job_number')
                    ->whereRaw('j1.franchise = ?', ['PC'])
                    ->whereRaw('j2.franchise = ?', ['CV'])
                    ->where('j1.is_dummy_wip', false)
                    ->where('j2.is_dummy_wip', false);
            })
            ->whereRaw('UPPER(TRIM(COALESCE(j1.plate_number, ""))) = UPPER(TRIM(COALESCE(j2.plate_number, "")))')
            ->whereNotNull('j1.plate_number')
            ->where('j1.plate_number', '!=', '')
            ->select(
                'j1.job_number',
                'j1.id as pc_id', 'j1.plate_number as pc_plate', 'j1.status as pc_status', 
                'j1.import_id as pc_import', 'j1.created_at as pc_created',
                'j2.id as cv_id', 'j2.plate_number as cv_plate', 'j2.status as cv_status', 
                'j2.import_id as cv_import', 'j2.created_at as cv_created'
            )
            ->orderBy('j1.job_number')
            ->get();

        $this->info("Found {$dupes->count()} cross-franchise duplicates.");

        if ($dupes->isEmpty()) {
            return 0;
        }

        // Group by source
        $byImport = [];
        foreach ($dupes as $d) {
            $key = "PC:{$d->pc_import} + CV:{$d->cv_import}";
            $byImport[$key] = ($byImport[$key] ?? 0) + 1;
        }

        $this->newLine();
        $this->info('Breakdown by import source:');
        foreach ($byImport as $key => $count) {
            $this->line("  {$key}: {$count} records");
        }
        $this->newLine();

        // Analysis
        $deleteViaImportStrategy = false;
        if ($deleteImportId) {
            $deleteViaImportStrategy = true;
            $this->info("Strategy: Delete records from Import #{$deleteImportId}");
            $this->newLine();
        }

        $toDelete = [];
        $toManual = [];

        foreach ($dupes as $d) {
            $decide = null;

            if ($deleteViaImportStrategy) {
                // Delete records from the specified import
                if ((int)$d->pc_import === (int)$deleteImportId) {
                    $decide = ['delete_id' => $d->pc_id, 'delete_franchise' => 'PC', 'keep_id' => $d->cv_id, 'keep_franchise' => 'CV'];
                } elseif ((int)$d->cv_import === (int)$deleteImportId) {
                    $decide = ['delete_id' => $d->cv_id, 'delete_franchise' => 'CV', 'keep_id' => $d->pc_id, 'keep_franchise' => 'PC'];
                }
            }

            if ($decide) {
                $toDelete[] = $decide;
            } else {
                $toManual[] = $d;
            }
        }

        // Show what will be deleted
        if (!empty($toDelete)) {
            $this->info("Auto-fixable: " . count($toDelete) . " records");
            $this->newLine();

            $progressBar = $this->output->createProgressBar(count($toDelete));
            $progressBar->start();

            foreach ($toDelete as $fix) {
                $job = Job::find($fix['delete_id']);
                if ($job) {
                    if (!$isDryRun) {
                        \Log::info("FIX_CROSS_FRANCHISE: Deleting job {$job->id} ({$job->job_number}) franchise={$fix['delete_franchise']}. Keeping ID={$fix['keep_id']} ({$fix['keep_franchise']}).");
                        $job->delete();
                    }
                    $this->line(" Deleted {$job->job_number} (ID:{$fix['delete_id']}, {$fix['delete_franchise']}) — kept ID:{$fix['keep_id']} ({$fix['keep_franchise']})");
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);
        }

        // Show manual cases
        if (!empty($toManual)) {
            $this->warn("Needs manual review: " . count($toManual) . " records");
            $this->newLine();

            foreach ($toManual as $d) {
                $this->line("WIP: {$d->job_number}");
                $this->line("  PC (ID:{$d->pc_id}): {$d->pc_plate} | {$d->pc_status} | Import:{$d->pc_import}");
                $this->line("  CV (ID:{$d->cv_id}): {$d->cv_plate} | {$d->cv_status} | Import:{$d->cv_import}");
                $this->line("");
            }
        }

        // Summary
        $this->newLine();
        $this->info('═══════ SUMMARY ═══════');
        $this->table(
            ['Category', 'Count'],
            [
                ['Auto-fixable (delete Import #' . ($deleteImportId ?? '?') . ')', count($toDelete)],
                ['Manual review needed', count($toManual)],
                ['Total', $dupes->count()],
            ]
        );

        if ($isDryRun) {
            $this->newLine();
            $this->warn('DRY RUN — no changes made. Re-run without --dry-run to apply.');
        }

        return 0;
    }
}
