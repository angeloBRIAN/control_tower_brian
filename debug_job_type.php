<?php

use App\Models\Job;

echo "--- Debugging Job Types ---\n";

$uninvoicedCount = Job::uninvoiced()->count();
echo "Uninvoiced Jobs Count: $uninvoicedCount\n";

if ($uninvoicedCount > 0) {
    echo "Sample Uninvoiced Job Types:\n";
    $jobs = Job::uninvoiced()->take(10)->get(['id', 'job_number', 'job_type', 'status']);
    foreach ($jobs as $job) {
        echo "ID: {$job->id}, No: {$job->job_number}, Type: '{$job->job_type}', Status: {$job->status}\n";
    }

    echo "\nRunning Group By Query:\n";
    try {
        $results = Job::uninvoiced()
            ->selectRaw('COALESCE(NULLIF(job_type, ""), "Unspecified") as type, COUNT(*) as count')
            ->groupBy('type')
            ->get();
        
        foreach ($results as $row) {
            echo "Type: {$row->type}, Count: {$row->count}\n";
        }
    } catch (\Exception $e) {
        echo "Query Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "No uninvoiced jobs found.\n";
}
