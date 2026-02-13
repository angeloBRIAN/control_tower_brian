<?php

use App\Http\Controllers\DashboardSettingsController;
use Illuminate\Http\Request;
use App\Models\User;

echo "--- Testing Monthly Completion Widget Data ---\n";

// Mock User and Request
$user = User::first(); 
auth()->login($user);

$controller = new DashboardSettingsController();
$request = new Request([
    'month' => now()->month,
    'year' => now()->year
]);

// Use reflection to access protected method
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('getMonthlyCompletionData');
$method->setAccessible(true);

try {
    $data = $method->invoke($controller, $request);
    echo "Data fetched successfully:\n";
    print_r($data);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
