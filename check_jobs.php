<?php

$wips = ['25047','26298','25809','24677','24195','23542','22981','22808','22742','22452','22483','22262','22161','21926'];

// Run within Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jobs = \App\Models\Job::whereIn('job_number', $wips)->get();

if ($jobs->isEmpty()) {
    echo "None of these jobs exist in the database!\n";
} else {
    foreach ($jobs as $job) {
        echo str_pad($job->job_number, 8) . " | Franchise: " . str_pad($job->franchise, 2) . " | Status: " . str_pad($job->status, 12) . " | Dummy: " . ($job->is_dummy_wip ? 'Y' : 'N') . " | WorkStatus: " . str_pad(substr($job->work_status, 0, 30), 30) . " | InvoiceNo: " . $job->invoice_number . " | InvDate: " . ($job->invoice_date ? $job->invoice_date->format('Y-m-d') : 'NULL') . "\n";
    }
    
    echo "\nTotal found: " . $jobs->count() . " out of " . count($wips) . "\n";
    
    $missing = array_diff($wips, $jobs->pluck('job_number')->toArray());
    if (!empty($missing)) {
        echo "Missing WIPs: " . implode(', ', $missing) . "\n";
    }
}
