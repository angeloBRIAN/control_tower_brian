<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\Vehicle;
use Illuminate\Console\Command;

class CleanCustomerNames extends Command
{
    protected $signature = 'data:clean-customer-names';
    protected $description = 'Clean up customer names by removing special characters at start/end';

    public function handle()
    {
        $this->info('Cleaning customer names...');
        
        // Clean jobs
        $jobsUpdated = 0;
        Job::whereNotNull('customer_name')->chunkById(100, function ($jobs) use (&$jobsUpdated) {
            foreach ($jobs as $job) {
                $original = $job->customer_name;
                $cleaned = $this->cleanName($original);
                
                if ($cleaned !== $original && !empty($cleaned)) {
                    $job->customer_name = $cleaned;
                    $job->saveQuietly(); // Skip auditing for bulk update
                    $jobsUpdated++;
                    $this->line("  Job {$job->job_number}: '{$original}' -> '{$cleaned}'");
                }
            }
        });
        
        $this->info("Jobs cleaned: {$jobsUpdated}");
        
        // Clean vehicles
        $vehiclesUpdated = 0;
        Vehicle::whereNotNull('customer_name')->chunkById(100, function ($vehicles) use (&$vehiclesUpdated) {
            foreach ($vehicles as $vehicle) {
                $original = $vehicle->customer_name;
                $cleaned = $this->cleanName($original);
                
                if ($cleaned !== $original && !empty($cleaned)) {
                    $vehicle->customer_name = $cleaned;
                    $vehicle->saveQuietly();
                    $vehiclesUpdated++;
                    $this->line("  Vehicle {$vehicle->plate_number}: '{$original}' -> '{$cleaned}'");
                }
            }
        });
        
        $this->info("Vehicles cleaned: {$vehiclesUpdated}");
        $this->info('Done!');
        
        return 0;
    }

    private function cleanName(?string $name): string
    {
        if (empty($name)) {
            return '';
        }
        
        // Trim whitespace
        $cleaned = trim($name);
        
        // Remove backticks, quotes, and other special chars from start/end
        $cleaned = preg_replace('/^[\`\'\"\s\*\#\@\!\~]+/', '', $cleaned);
        $cleaned = preg_replace('/[\`\'\"\s\*\#\@\!\~]+$/', '', $cleaned);
        
        // Normalize multiple spaces to single space
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        
        return trim($cleaned);
    }
}
