<?php

namespace App\Console\Commands;

use App\Models\Job;
use Illuminate\Console\Command;

class FixDummyWips extends Command
{
    protected $signature = 'wip:fix-dummies 
        {--dry-run : Preview changes without modifying anything}
        {--category=all : Limit to specific category (same_plate|typo|franchise|diff_vehicle)}
        {--id= : Fix specific dummy job by ID}';

    protected $description = 'Analyze and fix dummy WIP jobs (is_dummy_wip=true)';

    private array $stats = [
        'total' => 0,
        'same_plate' => ['count' => 0, 'fixable' => 0],
        'typo' => ['count' => 0, 'fixable' => 0],
        'franchise' => ['count' => 0, 'fixable' => 0],
        'diff_vehicle' => ['count' => 0, 'fixable' => 0],
    ];

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run') !== false;
        $category = $this->option('category');
        $specificId = $this->option('id');

        if ($isDryRun) {
            $this->warn('═══ DRY RUN MODE ═══ No changes will be made');
            $this->newLine();
        }

        $dummies = Job::where('is_dummy_wip', true);
        
        if ($specificId) {
            $dummies->where('id', $specificId);
        }
        
        $dummies = $dummies->orderBy('created_at', 'desc')->get();
        $this->stats['total'] = $dummies->count();

        if ($dummies->isEmpty()) {
            $this->info('No dummy WIP jobs found.');
            return 0;
        }

        $this->info("Found {$dummies->count()} dummy WIP jobs to process.");
        $this->newLine();

        foreach ($dummies as $dummy) {
            $origWip = preg_replace('/-(DUP|WRONG)-\d+$/', '', $dummy->job_number);
            $type = str_contains($dummy->job_number, '-WRONG-') ? 'WRONG' : 'DUP';
            $realJob = Job::where('job_number', $origWip)->first();

            $dummyPlate = strtoupper(trim($dummy->plate_number ?? ''));
            $realPlate = $realJob ? strtoupper(trim($realJob->plate_number ?? '')) : 'N/A';
            
            $similarity = 0;
            if ($realJob && $dummyPlate !== '') {
                similar_text($dummyPlate, $realPlate, $similarity);
            }

            // Determine category
            $categoryType = $this->categorize($dummyPlate, $realPlate, $similarity, $dummy, $realJob);
            
            if ($category !== 'all' && $categoryType !== $category) {
                continue;
            }

            $this->stats[$categoryType]['count']++;

            $this->line("────────────────────────────────────────────");
            $this->line(" <fg=cyan>{$dummy->job_number}</>");
            $this->line(" Type: {$type} | Category: <fg=yellow>{$categoryType}</>");
            $this->line(" Plate: {$dummyPlate} (Frc: {$dummy->franchise})");
            
            if ($realJob) {
                $this->line(" Real:  {$origWip} → Plate: {$realPlate} (Frc: {$realJob->franchise}) [{$realJob->status}]");
            } else {
                $this->line(" Real:  <fg=red>NOT FOUND</> (WIP {$origWip} has no job)");
            }

            // Apply fix based on category
            $fixResult = $this->applyFix($categoryType, $dummy, $realJob, $isDryRun);
            
            if ($fixResult['fixable']) {
                $this->stats[$categoryType]['fixable']++;
                if ($fixResult['action']) {
                    $this->line(" <fg=green>✓ {$fixResult['action']}</>");
                }
            } else {
                $this->line(" <fg=yellow>⚠ Manual review needed: {$fixResult['reason']}</>");
            }
        }

        // Summary
        $this->newLine();
        $this->info('═══════ SUMMARY ═══════');
        $this->table(
            ['Category', 'Total', 'Auto-Fixable', 'Needs Review'],
            [
                ['SAME_PLATE', $this->stats['same_plate']['count'], $this->stats['same_plate']['fixable'], $this->stats['same_plate']['count'] - $this->stats['same_plate']['fixable']],
                ['TYPO', $this->stats['typo']['count'], $this->stats['typo']['fixable'], $this->stats['typo']['count'] - $this->stats['typo']['fixable']],
                ['FRANCHISE', $this->stats['franchise']['count'], $this->stats['franchise']['fixable'], $this->stats['franchise']['count'] - $this->stats['franchise']['fixable']],
                ['DIFF_VEHICLE', $this->stats['diff_vehicle']['count'], $this->stats['diff_vehicle']['fixable'], $this->stats['diff_vehicle']['count'] - $this->stats['diff_vehicle']['fixable']],
            ]
        );

        if ($isDryRun) {
            $this->newLine();
            $this->warn('This was a DRY RUN. Run with --dry-run=false to apply changes.');
        }

        return 0;
    }

    private function categorize(string $dummyPlate, string $realPlate, float $similarity, Job $dummy, ?Job $realJob): string
    {
        if (!$realJob) return 'diff_vehicle';
        
        // Same plate → system bug, should never have been created
        if ($dummyPlate === $realPlate) {
            return 'same_plate';
        }
        
        // Franchise mismatch
        if ($realJob->franchise !== $dummy->franchise) {
            return 'franchise';
        }
        
        // Typo (high similarity, same vehicle)
        if ($similarity > 70) {
            return 'typo';
        }
        
        // Different vehicle
        return 'diff_vehicle';
    }

    private function applyFix(string $category, Job $dummy, ?Job $realJob, bool $isDryRun): array
    {
        return match ($category) {
            'same_plate' => $this->fixSamePlate($dummy, $realJob, $isDryRun),
            'typo' => $this->fixTypo($dummy, $realJob, $isDryRun),
            'franchise' => $this->fixFranchise($dummy, $realJob, $isDryRun),
            'diff_vehicle' => $this->fixDiffVehicle($dummy, $realJob, $isDryRun),
            default => ['fixable' => false, 'action' => null, 'reason' => 'Unknown category'],
        };
    }

    /**
     * SAME_PLATE: Dummy has same plate as real job → safe to delete
     */
    private function fixSamePlate(Job $dummy, ?Job $realJob, bool $isDryRun): array
    {
        if (!$realJob) {
            return $this->manual('Real job not found');
        }

        // Log what data would be lost
        $valuableFields = $this->getValuableFields($dummy, $realJob);
        
        if (!empty($valuableFields)) {
            $fieldList = implode(', ', array_keys($valuableFields));
            $this->line(" <fg=yellow>⚠ Dummy has unique data: {$fieldList}</>");
            $this->line("   These values will be LOST if deleted.");
        }

        if (!$isDryRun) {
            $this->deleteDummy($dummy, "SAME_PLATE: Duplicate of WIP {$realJob->job_number} (same plate {$dummy->plate_number})");
        }

        return [
            'fixable' => true,
            'action' => ($isDryRun ? '[DRY RUN] Would DELETE' : 'DELETED') . " dummy WIP {$dummy->job_number} (same plate as real job)",
            'reason' => null,
        ];
    }

    /**
     * TYPO: Same vehicle, slight plate difference → safe to delete
     */
    private function fixTypo(Job $dummy, ?Job $realJob, bool $isDryRun): array
    {
        if (!$realJob) {
            return $this->manual('Real job not found');
        }

        if ($realJob->status === 'invoiced') {
            // Real job is invoiced, dummy is uninvoiced → dummy can be deleted
            if (!$isDryRun) {
                $this->deleteDummy($dummy, "TYPO: Real WIP {$realJob->job_number} is invoiced with plate {$realJob->plate_number}");
            }
            
            return [
                'fixable' => true,
                'action' => ($isDryRun ? '[DRY RUN] Would DELETE' : 'DELETED') . " dummy (real job is invoiced, plate typo: {$dummy->plate_number} vs {$realJob->plate_number})",
                'reason' => null,
            ];
        }

        // Both uninvoiced → need to check which has the correct plate
        // Typically, the REAL job (without suffix) has the authoritative data
        $valuableFields = $this->getValuableFields($dummy, $realJob);
        
        if (!empty($valuableFields)) {
            // Transfer unique data from dummy to real job, then delete dummy
            if (!$isDryRun) {
                foreach ($valuableFields as $field => $value) {
                    $realJob->update([$field => $value]);
                    $this->line("   Transferred {$field}='{$value}' to real job");
                }
                $this->deleteDummy($dummy, "TYPO: Data merged into real WIP {$realJob->job_number}");
            }
            
            return [
                'fixable' => true,
                'action' => ($isDryRun ? '[DRY RUN] Would MERGE data and DELETE' : 'MERGED and DELETED') . " dummy (transferred fields to real job)",
                'reason' => null,
            ];
        }

        // No unique data → safe to delete
        if (!$isDryRun) {
            $this->deleteDummy($dummy, "TYPO: Plate typo {$dummy->plate_number} vs {$realJob->plate_number}");
        }

        return [
            'fixable' => true,
            'action' => ($isDryRun ? '[DRY RUN] Would DELETE' : 'DELETED') . " dummy (no unique data, plate typo)",
            'reason' => null,
        ];
    }

    /**
     * FRANCHISE: Wrong franchise used during import
     */
    private function fixFranchise(Job $dummy, ?Job $realJob, bool $isDryRun): array
    {
        if (!$realJob) {
            return $this->manual('Real job not found');
        }

        // Check if plates are similar (typo + franchise issue)
        $dummyPlate = strtoupper(trim($dummy->plate_number ?? ''));
        $realPlate = strtoupper(trim($realJob->plate_number ?? ''));
        
        if ($dummyPlate !== $realPlate) {
            // Different plates → can't auto-fix, different vehicles entirely
            return $this->manual("Franchise mismatch ({$dummy->franchise} vs {$realJob->franchise}) AND different plates ({$dummyPlate} vs {$realPlate}). Verify which vehicle owns WIP {$realJob->job_number}.");
        }

        // Same plate but wrong franchise → update franchise on real job
        if (!$isDryRun) {
            $realJob->update(['franchise' => $dummy->franchise]);
            $this->deleteDummy($dummy, "FRANCHISE: Corrected franchise to {$dummy->franchise} on WIP {$realJob->job_number}");
        }

        return [
            'fixable' => true,
            'action' => ($isDryRun ? '[DRY RUN] Would UPDATE franchise and DELETE' : 'UPDATED franchise and DELETED') . " dummy",
            'reason' => null,
        ];
    }

    /**
     * DIFF_VEHICLE: Legitimate conflict → needs manual review
     */
    private function fixDiffVehicle(Job $dummy, ?Job $realJob, bool $isDryRun): array
    {
        return $this->manual("Different vehicles share same WIP. Review via WIP Conflict Report or determine which vehicle owns WIP {$dummy->job_number}.");
    }

    private function deleteDummy(Job $dummy, string $reason): void
    {
        JobActivity::log($dummy, JobActivity::ACTION_DELETED, "Deleted via wip:fix-dummies. Reason: {$reason}");
        $dummy->delete();
    }

    private function getValuableFields(Job $dummy, Job $realJob): array
    {
        $checkFields = [
            'customer_name', 'customer_address', 'service_advisor', 'foreman',
            'technician', 'job_type', 'job_description', 'description',
            'work_order_number', 'unit_type',
        ];
        
        $valuable = [];
        foreach ($checkFields as $field) {
            $dummyVal = $dummy->{$field} ?? null;
            $realVal = $realJob->{$field} ?? null;
            
            if (!empty($dummyVal) && empty($realVal)) {
                $valuable[$field] = $dummyVal;
            }
        }
        
        return $valuable;
    }

    private function manual(string $reason): array
    {
        return ['fixable' => false, 'action' => null, 'reason' => $reason];
    }
}
