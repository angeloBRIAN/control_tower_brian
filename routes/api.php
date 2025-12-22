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

// Vehicle lookup API for job creation form
Route::get('/vehicles/lookup', function (Request $request) {
    $plate = $request->query('plate');
    
    if (empty($plate)) {
        return response()->json(['found' => false]);
    }
    
    $vehicle = Vehicle::where('plate_number', $plate)
        ->orWhere('plate_number', 'LIKE', str_replace(' ', '', $plate))
        ->first();
    
    if ($vehicle) {
        return response()->json([
            'found' => true,
            'model' => $vehicle->model,
            'customer_name' => $vehicle->customer_name,
        ]);
    }
    
    return response()->json(['found' => false]);
});
