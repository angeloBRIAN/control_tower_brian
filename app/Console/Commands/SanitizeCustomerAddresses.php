<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;

class SanitizeCustomerAddresses extends Command
{
    protected $signature = 'customers:sanitize-addresses 
                            {--dry-run : Show what would be changed without actually saving}';

    protected $description = 'Sanitize customer addresses by removing duplicate parts';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Scanning customer addresses for duplicates...');
        
        $customers = Customer::whereNotNull('address')
            ->orWhereNotNull('address_1')
            ->get();
        
        $updatedCount = 0;
        $addressFields = ['address', 'address_1', 'address_2', 'address_3', 'address_4', 'address_5'];
        
        foreach ($customers as $customer) {
            $updated = false;
            
            // Check each address field for internal duplication
            foreach ($addressFields as $field) {
                $value = $customer->$field;
                if (empty($value)) continue;
                
                $cleaned = $this->deduplicateAddress($value);
                
                if ($cleaned !== $value) {
                    if ($dryRun) {
                        $this->line("Customer ID {$customer->id} ({$customer->name}):");
                        $this->line("  {$field}: \"{$value}\"");
                        $this->line("  -> \"{$cleaned}\"");
                        $this->newLine();
                    } else {
                        $customer->$field = $cleaned;
                        $updated = true;
                    }
                }
            }
            
            // Also check if multiple address fields contain the same value
            $uniqueValues = [];
            foreach ($addressFields as $field) {
                $value = trim($customer->$field ?? '');
                if (empty($value)) continue;
                
                $normalized = strtolower($value);
                if (isset($uniqueValues[$normalized])) {
                    // This is a duplicate field, clear it
                    if ($dryRun) {
                        $this->line("Customer ID {$customer->id}: Duplicate field {$field} = \"{$value}\"");
                    } else {
                        $customer->$field = null;
                        $updated = true;
                    }
                } else {
                    $uniqueValues[$normalized] = $value;
                }
            }
            
            if ($updated && !$dryRun) {
                $customer->save();
                $updatedCount++;
            } elseif ($updated) {
                $updatedCount++;
            }
        }
        
        if ($dryRun) {
            $this->warn("DRY RUN: Would update {$updatedCount} customers.");
            $this->info('Run without --dry-run to apply changes.');
        } else {
            $this->info("Successfully sanitized {$updatedCount} customer address records.");
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Remove duplicate parts from a single address string
     * e.g., "Street A, City, Street A, City" -> "Street A, City"
     */
    private function deduplicateAddress(string $address): string
    {
        // Split by comma
        $parts = array_map('trim', explode(',', $address));
        
        // Method 1: Check if the address is exactly repeated
        $count = count($parts);
        if ($count >= 2 && $count % 2 === 0) {
            $half = $count / 2;
            $firstHalf = array_slice($parts, 0, $half);
            $secondHalf = array_slice($parts, $half);
            
            // Compare halves (case-insensitive)
            $match = true;
            for ($i = 0; $i < $half; $i++) {
                if (strtolower($firstHalf[$i]) !== strtolower($secondHalf[$i])) {
                    $match = false;
                    break;
                }
            }
            
            if ($match) {
                return implode(', ', $firstHalf);
            }
        }
        
        // Method 2: Remove exact duplicate parts in sequence
        $uniqueParts = [];
        $seenNormalized = [];
        
        foreach ($parts as $part) {
            $normalized = strtolower(trim($part));
            if (!isset($seenNormalized[$normalized])) {
                $seenNormalized[$normalized] = true;
                $uniqueParts[] = $part;
            }
        }
        
        return implode(', ', $uniqueParts);
    }
}
