<?php

namespace App\Console\Commands;

use App\Models\CustomerMergeSuggestion;
use App\Models\Job;
use Illuminate\Console\Command;

class FindCustomerDuplicates extends Command
{
    protected $signature = 'customers:find-duplicates {--force : Clear existing suggestions and re-scan}';
    protected $description = 'Find potential duplicate customer names and store as merge suggestions';

    public function handle(): int
    {
        // If force, clear pending suggestions
        if ($this->option('force')) {
            CustomerMergeSuggestion::pending()->delete();
            $this->info('Cleared existing pending suggestions.');
        }

        // Skip if we already have pending suggestions
        $pendingCount = CustomerMergeSuggestion::pending()->count();
        if ($pendingCount > 0 && !$this->option('force')) {
            $this->info("Already have {$pendingCount} pending suggestions. Use --force to re-scan.");
            return Command::SUCCESS;
        }

        $this->info('Scanning for duplicate customer names...');

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

        // Compare each pair (optimized - only forward comparisons)
        foreach ($customers as $i => $customerA) {
            $processed++;
            
            if ($processed % 100 === 0) {
                $this->info("Processing: {$processed}/{$total}...");
            }

            $nameA = trim(strtolower($customerA->customer_name));
            
            // Skip very short names
            if (strlen($nameA) < 3) {
                continue;
            }

            foreach ($customers->slice($i + 1) as $customerB) {
                $nameB = trim(strtolower($customerB->customer_name));
                
                // Skip very short names
                if (strlen($nameB) < 3) {
                    continue;
                }

                // Quick length check - if too different, skip
                $lenDiff = abs(strlen($nameA) - strlen($nameB));
                if ($lenDiff > 5) {
                    continue;
                }

                // Calculate similarity
                similar_text($nameA, $nameB, $similarity);
                
                // Only suggest if very similar (>80%)
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
            $this->info('No duplicate suggestions found.');
            return Command::SUCCESS;
        }

        // Insert in batches
        $chunks = array_chunk($suggestions, 100);
        foreach ($chunks as $chunk) {
            CustomerMergeSuggestion::insert($chunk);
        }

        $this->info("Found " . count($suggestions) . " potential duplicates.");
        return Command::SUCCESS;
    }
}
