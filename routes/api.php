<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Vehicle;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Vehicle lookup API for job creation, booking, and towing forms
Route::get('/vehicles/lookup', function (Request $request) {
    $plate = $request->query('plate');
    
    if (empty($plate)) {
        return response()->json(['found' => false]);
    }
    
    // Normalize plate: remove spaces and search case-insensitive
    $normalizedPlate = strtoupper(preg_replace('/\s+/', '', $plate));
    
    $vehicle = Vehicle::whereRaw('UPPER(REPLACE(plate_number, " ", "")) = ?', [$normalizedPlate])
        ->first();
    
    if ($vehicle) {
        return response()->json([
            'found' => true,
            'model' => $vehicle->model,
            'customer_name' => $vehicle->customer_name,
            'vin' => $vehicle->vin,
        ]);
    }
    
    return response()->json(['found' => false]);
});

