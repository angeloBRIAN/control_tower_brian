<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Job;
use Illuminate\Http\Request;

class OrphanVehicleReportController extends Controller
{
    /**
     * Display orphan vehicles (vehicles with 0 jobs)
     */
    public function index(Request $request)
    {
        $query = Vehicle::withCount('jobs')
            ->having('jobs_count', '=', 0);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $orphans = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        // Stats
        $totalOrphans = Vehicle::withCount('jobs')
            ->having('jobs_count', '=', 0)
            ->count();
            
        $totalVehicles = Vehicle::count();

        return view('reports.orphan-vehicles', compact('orphans', 'totalOrphans', 'totalVehicles'));
    }

    /**
     * Delete an orphan vehicle
     */
    public function destroy(Vehicle $vehicle)
    {
        // Safety check: only delete if no jobs
        $jobCount = Job::where('plate_number', $vehicle->plate_number)->count();
        if ($jobCount > 0) {
            return redirect()->back()->with('error', "Cannot delete: {$vehicle->plate_number} has {$jobCount} associated jobs.");
        }

        $plateName = $vehicle->plate_number;
        $vehicle->delete();
        
        return redirect()->back()->with('success', "Vehicle {$plateName} deleted successfully.");
    }

    /**
     * Bulk delete orphan vehicles
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'vehicle_ids' => 'required|array|min:1',
            'vehicle_ids.*' => 'exists:vehicles,id',
        ]);

        $deleted = 0;
        $skipped = 0;

        foreach ($request->vehicle_ids as $id) {
            $vehicle = Vehicle::find($id);
            if (!$vehicle) continue;

            $jobCount = Job::where('plate_number', $vehicle->plate_number)->count();
            if ($jobCount > 0) {
                $skipped++;
                continue;
            }

            $vehicle->delete();
            $deleted++;
        }

        return redirect()->back()->with('success', "Deleted {$deleted} orphan vehicles. Skipped {$skipped} (have jobs).");
    }
}
