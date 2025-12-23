<?php

namespace App\Console\Commands;

use App\Models\CustomerMergeSuggestion;
use App\Models\DismissedDuplicateGroup;
use App\Models\Job;
use Illuminate\Console\Command;

class FindCustomerDuplicates extends Command
{
    protected $signature = 'customers:find-duplicates {--force : Clear pending and re-scan}';
    protected $description = 'Find potential duplicate customer names and store as merge suggestions';

    public function handle(): int
    {
        // If force, clear pending suggestions only (keep merged/ignored history)
        if ($this->option('force')) {
            CustomerMergeSuggestion::pending()->delete();
            $this->info('Cleared existing pending suggestions.');
        }

        $this->info('Scanning for duplicate customer names...');

        // Get already processed pairs (merged or ignored) to skip
        $processedPairs = CustomerMergeSuggestion::whereIn('status', ['merged', 'ignored'])
            ->get()
            ->map(fn($s) => $this->normalizeKey($s->customer_name_a, $s->customer_name_b))
            ->toArray();

        // Also check DismissedDuplicateGroup for old dismissals
        $dismissedGroups = DismissedDuplicateGroup::pluck('group_hash')->toArray();

        // Get unique customer names with job counts
        $customers = Job::whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->selectRaw('customer_name, COUNT(*) as jobs_count')
            ->groupBy('customer_name')
            ->orderBy('customer_name')
            ->get();

        $this->info("Found {$customers->count()} unique customer names.");

        $suggestions = [];
        $processed = 0;
        $total = $customers->count();

        foreach ($customers as $i => $customerA) {
            $processed++;
            
            if ($processed % 100 === 0) {
                $this->info("Processing: {$processed}/{$total}...");
            }

            $nameA = trim(strtolower($customerA->customer_name));
            
            if (strlen($nameA) < 3) {
                continue;
            }

            foreach ($customers->slice($i + 1) as $customerB) {
                $nameB = trim(strtolower($customerB->customer_name));
                
                if (strlen($nameB) < 3) {
                    continue;
                }

                // Skip if already processed (merged/ignored)
                $pairKey = $this->normalizeKey($customerA->customer_name, $customerB->customer_name);
                if (in_array($pairKey, $processedPairs)) {
                    continue;
                }

                // Skip if in dismissed groups
                $groupHash = DismissedDuplicateGroup::generateHash([$customerA->customer_name, $customerB->customer_name]);
                if (in_array($groupHash, $dismissedGroups)) {
                    continue;
                }

                // Quick length check
                $lenDiff = abs(strlen($nameA) - strlen($nameB));
                if ($lenDiff > 5) {
                    continue;
                }

                // Calculate similarity
                similar_text($nameA, $nameB, $similarity);
                
                if ($similarity >= 80) {
                    $suggestions[] = [
                        'customer_name_a' => $customerA->customer_name,
                        'customer_name_b' => $customerB->customer_name,
                        'similarity_score' => round($similarity, 2),
                        'jobs_count_a' => $customerA->jobs_count,
                        'jobs_count_b' => $customerB->jobs_count,
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (empty($suggestions)) {
            $this->info('No new duplicate suggestions found.');
            return Command::SUCCESS;
        }

        // Clear old pending and insert new
        CustomerMergeSuggestion::pending()->delete();
        
        $chunks = array_chunk($suggestions, 100);
        foreach ($chunks as $chunk) {
            CustomerMergeSuggestion::insert($chunk);
        }

        $this->info("Found " . count($suggestions) . " potential duplicates.");
        return Command::SUCCESS;
    }

    /**
     * Generate normalized key for pair to avoid duplicates
     */
    private function normalizeKey(string $a, string $b): string
    {
        $names = [strtolower(trim($a)), strtolower(trim($b))];
        sort($names);
        return implode('|', $names);
    }
}
