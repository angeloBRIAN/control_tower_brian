<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class DmsCustomerController extends Controller
{
    /**
     * Display listing of DMS-imported customers
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter');
        $sortField = $request->input('sort', 'name');
        $sortDir = $request->input('dir', 'asc');
        
        $query = Customer::query();
        
        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('dms_magic', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }
        
        // Filters
        if ($filter === 'dms_only') {
            $query->whereNotNull('dms_magic');
        } elseif ($filter === 'with_vehicles') {
            $query->has('linkedVehicles');
        } elseif ($filter === 'with_jobs') {
            $query->has('linkedJobs');
        } elseif ($filter === 'no_vehicles') {
            $query->doesntHave('linkedVehicles');
        }
        
        // Sorting
        $query->orderBy($sortField, $sortDir);
        
        // Get with counts
        $customers = $query
            ->withCount(['linkedVehicles', 'linkedJobs'])
            ->paginate(50)
            ->withQueryString();
        
        // Stats
        $stats = [
            'total' => Customer::count(),
            'dms_imported' => Customer::whereNotNull('dms_magic')->count(),
            'with_vehicles' => Customer::has('linkedVehicles')->count(),
            'with_jobs' => Customer::has('linkedJobs')->count(),
        ];
        
        return view('master.dms-customers.index', compact('customers', 'search', 'filter', 'sortField', 'sortDir', 'stats'));
    }

    /**
     * Show customer detail
     */
    public function show(Customer $customer)
    {
        $customer->load([
            'linkedVehicles',
            'linkedJobs' => fn($q) => $q->latest('job_date')->limit(20),
            'aliases',
        ]);
        
        return view('master.dms-customers.show', compact('customer'));
    }
}
