<?php

namespace App\Console\Commands;

use App\Models\CustomerSummary;
use App\Models\Job;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshCustomerSummaries extends Command
{
    protected $signature = 'customers:refresh-summaries';
    protected $description = 'Refresh the customer summaries cache table';

    public function handle(): int
    {
        $this->info('Refreshing customer summaries...');

        // Get all unique customer names
        $names = DB::table(
            DB::raw("(
                SELECT DISTINCT customer_name as name FROM vehicles WHERE customer_name IS NOT NULL AND customer_name != ''
                UNION
                SELECT DISTINCT customer_name as name FROM jobs WHERE customer_name IS NOT NULL AND customer_name != ''
            ) as customers")
        )->pluck('name');

        $this->info("Found {$names->count()} unique customers.");

        $bar = $this->output->createProgressBar($names->count());
        $bar->start();

        $batch = [];
        $batchSize = 100;

        foreach ($names as $name) {
            $vehicleCount = Vehicle::where('customer_name', $name)->count();
            $uninvoicedCount = Job::where('customer_name', $name)->where('status', 'uninvoiced')->count();
            $invoicedCount = Job::where('customer_name', $name)->where('status', 'invoiced')->count();
            $totalSales = Job::where('customer_name', $name)->where('status', 'invoiced')->sum('inv_ppn_meterai') ?? 0;
            $estimatedSales = Job::where('customer_name', $name)->where('status', 'uninvoiced')->sum('total_sales') ?? 0;

            $batch[] = [
                'name' => $name,
                'vehicle_count' => $vehicleCount,
                'job_count' => $uninvoicedCount + $invoicedCount,
                'uninvoiced_count' => $uninvoicedCount,
                'invoiced_count' => $invoicedCount,
                'total_sales' => $totalSales,
                'estimated_sales' => $estimatedSales,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                $this->upsertBatch($batch);
                $batch = [];
            }

            $bar->advance();
        }

        // Insert remaining
        if (!empty($batch)) {
            $this->upsertBatch($batch);
        }

        $bar->finish();
        $this->newLine();
        $this->info('Customer summaries refreshed!');

        return Command::SUCCESS;
    }

    private function upsertBatch(array $batch): void
    {
        foreach ($batch as $item) {
            CustomerSummary::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
