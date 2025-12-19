<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Job;
use App\Models\CustomerMergeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of unique customers from vehicles and jobs.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sortField = $request->input('sort', 'name');
        $sortDir = $request->input('dir', 'asc');

        // Get unique customer names from both vehicles and jobs
        $customersQuery = DB::table(
            DB::raw("(
                SELECT DISTINCT customer_name as name FROM vehicles WHERE customer_name IS NOT NULL AND customer_name != ''
                UNION
                SELECT DISTINCT customer_name as name FROM jobs WHERE customer_name IS NOT NULL AND customer_name != ''
            ) as customers")
        );

        if ($search) {
            $customersQuery->where('name', 'like', "%{$search}%");
        }

        // Get all customer names for counting
        $customerNames = (clone $customersQuery)->pluck('name')->toArray();
        
        // Now get paginated customers with counts
        $perPage = $request->input('per_page', 20);
        
        // For name sorting, use DB-level ordering
        if ($sortField === 'name') {
            $customers = $customersQuery
                ->orderBy('name', $sortDir === 'asc' ? 'asc' : 'desc')
                ->paginate($perPage)
                ->withQueryString();
        } else {
            // For other fields, get all and sort after enriching
            $customers = $customersQuery
                ->orderBy('name', 'asc')
                ->paginate($perPage)
                ->withQueryString();
        }

        // Add counts for each customer on current page
        $customerData = [];
        foreach ($customers as $customer) {
            $vehicleCount = Vehicle::where('customer_name', $customer->name)->count();
            $jobCount = Job::where('customer_name', $customer->name)->count();
            $salesAmount = Job::where('customer_name', $customer->name)
                ->where('status', 'invoiced')
                ->sum('inv_ppn_meterai') ?? 0;
            
            $customerData[] = (object)[
                'name' => $customer->name,
                'vehicle_count' => $vehicleCount,
                'job_count' => $jobCount,
                'uninvoiced_count' => Job::where('customer_name', $customer->name)->where('status', 'uninvoiced')->count(),
                'sales_amount' => $salesAmount,
                'estimated_sales' => Job::where('customer_name', $customer->name)
                    ->where('status', 'uninvoiced')
                    ->sum('total_sales') ?? 0,
            ];
        }

        // Sort by non-name fields if needed
        if ($sortField !== 'name' && in_array($sortField, ['vehicle_count', 'job_count', 'sales_amount'])) {
            usort($customerData, function($a, $b) use ($sortField, $sortDir) {
                $aVal = $a->$sortField;
                $bVal = $b->$sortField;
                if ($sortDir === 'asc') {
                    return $aVal <=> $bVal;
                }
                return $bVal <=> $aVal;
            });
        }

        return view('customers.index', [
            'customers' => $customers,
            'customerData' => $customerData,
            'search' => $search,
            'sortField' => $sortField,
            'sortDir' => $sortDir,
            'totalCustomers' => count($customerNames),
        ]);
    }

    /**
     * Display customer detail with related vehicles and jobs.
     */
    public function show(Request $request)
    {
        $customerName = $request->input('name');
        
        if (empty($customerName)) {
            return redirect()->route('customers.index')->with('error', 'Customer name is required');
        }

        // Get related vehicles
        $vehicles = Vehicle::where('customer_name', $customerName)
            ->withCount('jobs')
            ->orderBy('plate_number')
            ->get();

        // Get related jobs
        $jobs = Job::where('customer_name', $customerName)
            ->orderBy('job_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Summary stats - use inv_ppn_meterai for invoiced jobs
        $stats = [
            'total_vehicles' => $vehicles->count(),
            'total_jobs' => Job::where('customer_name', $customerName)->count(),
            'uninvoiced_jobs' => Job::where('customer_name', $customerName)->where('status', 'uninvoiced')->count(),
            'invoiced_jobs' => Job::where('customer_name', $customerName)->where('status', 'invoiced')->count(),
            'total_sales' => Job::where('customer_name', $customerName)->where('status', 'invoiced')->sum('inv_ppn_meterai') ?? 0,
            'estimated_sales' => Job::where('customer_name', $customerName)->where('status', 'uninvoiced')->sum('total_sales') ?? 0,
        ];

        return view('customers.show', [
            'customerName' => $customerName,
            'vehicles' => $vehicles,
            'jobs' => $jobs,
            'stats' => $stats,
        ]);
    }

    /**
     * Search customers by name (AJAX autocomplete).
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = DB::table(
            DB::raw("(
                SELECT DISTINCT customer_name as name FROM vehicles WHERE customer_name IS NOT NULL AND customer_name != ''
                UNION
                SELECT DISTINCT customer_name as name FROM jobs WHERE customer_name IS NOT NULL AND customer_name != ''
            ) as customers")
        )
        ->where('name', 'like', "%{$query}%")
        ->orderBy('name')
        ->limit(15)
        ->pluck('name');

        return response()->json($results);
    }

    /**
     * Find and show duplicate/similar customer names
     */
    public function duplicates()
    {
        // Get all unique customer names
        $allNames = DB::table(
            DB::raw("(
                SELECT DISTINCT customer_name as name FROM vehicles WHERE customer_name IS NOT NULL AND customer_name != ''
                UNION
                SELECT DISTINCT customer_name as name FROM jobs WHERE customer_name IS NOT NULL AND customer_name != ''
            ) as customers")
        )
        ->orderBy('name')
        ->pluck('name')
        ->toArray();

        // Find similar names (potential duplicates)
        $duplicateGroups = [];
        $processed = [];

        foreach ($allNames as $name1) {
            if (in_array($name1, $processed)) {
                continue;
            }

            $similar = [$name1];
            $normalized1 = $this->normalizeForComparison($name1);

            foreach ($allNames as $name2) {
                if ($name1 === $name2 || in_array($name2, $processed)) {
                    continue;
                }

                $normalized2 = $this->normalizeForComparison($name2);
                
                // Check similarity
                $levenshtein = levenshtein($normalized1, $normalized2);
                $maxLen = max(strlen($normalized1), strlen($normalized2));
                $similarity = $maxLen > 0 ? (1 - $levenshtein / $maxLen) * 100 : 0;

                // Also check similar_text percentage
                similar_text($normalized1, $normalized2, $percentSimilar);

                // If either method shows high similarity (>80%), group them
                if ($similarity > 80 || $percentSimilar > 80) {
                    $similar[] = $name2;
                    $processed[] = $name2;
                }
            }

            $processed[] = $name1;

            // Only include groups with 2+ names
            if (count($similar) >= 2) {
                // Get counts and SOURCE for each name
                $groupWithCounts = [];
                $dmsSourceCount = 0;
                $userSourceCount = 0;

                foreach ($similar as $n) {
                    $source = $this->detectDuplicateSource($n);
                    
                    // Count source types for classification
                    if ($source === 'dms_import') {
                        $dmsSourceCount++;
                    } else {
                        $userSourceCount++;
                    }

                    $groupWithCounts[] = [
                        'name' => $n,
                        'job_count' => Job::where('customer_name', $n)->count(),
                        'vehicle_count' => Vehicle::where('customer_name', $n)->count(),
                        'source' => $source,
                        'source_label' => $this->getSourceLabel($source),
                    ];
                }

                // Classify the group: 
                // - If 2+ entries from DMS (invoiced/uninvoiced) = DMS_ISSUE (fix in main system)
                // - If any entry from progress/manual = USER_MISTAKE
                $groupClassification = $dmsSourceCount >= 2 ? 'DMS_ISSUE' : 'USER_MISTAKE';

                $duplicateGroups[] = [
                    'entries' => $groupWithCounts,
                    'classification' => $groupClassification,
                    'dms_count' => $dmsSourceCount,
                    'user_count' => $userSourceCount,
                ];
            }
        }

        return view('customers.duplicates', [
            'duplicateGroups' => $duplicateGroups,
            'totalGroups' => count($duplicateGroups),
            'dmsIssueCount' => collect($duplicateGroups)->where('classification', 'DMS_ISSUE')->count(),
            'userMistakeCount' => collect($duplicateGroups)->where('classification', 'USER_MISTAKE')->count(),
        ]);
    }

    /**
     * Merge selected customer names into canonical name
     */
    public function merge(Request $request)
    {
        $request->validate([
            'names_to_merge' => 'required|array|min:1',
            'canonical_name' => 'required|string|min:1',
        ]);

        $namesToMerge = $request->input('names_to_merge');
        $canonicalName = trim($request->input('canonical_name'));

        $totalJobsUpdated = 0;
        $totalVehiclesUpdated = 0;

        foreach ($namesToMerge as $oldName) {
            if ($oldName === $canonicalName) {
                continue; // Skip if same as canonical
            }

            // Detect source type by checking linked imports
            $sourceType = $this->detectDuplicateSource($oldName);

            // Count before update
            $jobsCount = Job::where('customer_name', $oldName)->count();
            $vehiclesCount = Vehicle::where('customer_name', $oldName)->count();

            // Update jobs
            Job::where('customer_name', $oldName)
                ->update(['customer_name' => $canonicalName]);

            // Update vehicles
            Vehicle::where('customer_name', $oldName)
                ->update(['customer_name' => $canonicalName]);

            // Log the merge operation
            CustomerMergeLog::create([
                'old_name' => $oldName,
                'canonical_name' => $canonicalName,
                'source_type' => $sourceType,
                'jobs_updated' => $jobsCount,
                'vehicles_updated' => $vehiclesCount,
                'merged_by' => auth()->user()?->name ?? 'System',
                'notes' => "Merged from duplicate detection. Source: {$sourceType}",
            ]);

            $totalJobsUpdated += $jobsCount;
            $totalVehiclesUpdated += $vehiclesCount;
        }

        return redirect()->route('customers.duplicates')
            ->with('success', "Merged successfully! Updated {$totalJobsUpdated} jobs and {$totalVehiclesUpdated} vehicles to '{$canonicalName}'. Changes have been logged for reporting.");
    }

    /**
     * Merge multiple groups of customer names in batch
     */
    public function mergeBatch(Request $request)
    {
        $request->validate([
            'groups' => 'required|array|min:1',
        ]);

        $groups = $request->input('groups', []);
        $totalJobsUpdated = 0;
        $totalVehiclesUpdated = 0;
        $groupsMerged = 0;

        foreach ($groups as $groupData) {
            $namesToMerge = $groupData['names'] ?? [];
            $canonicalName = trim($groupData['canonical'] ?? '');

            if (empty($namesToMerge) || empty($canonicalName)) {
                continue; // Skip incomplete groups
            }

            foreach ($namesToMerge as $oldName) {
                if ($oldName === $canonicalName) {
                    continue;
                }

                // Detect source type
                $sourceType = $this->detectDuplicateSource($oldName);

                // Count before update
                $jobsCount = Job::where('customer_name', $oldName)->count();
                $vehiclesCount = Vehicle::where('customer_name', $oldName)->count();

                // Update records
                Job::where('customer_name', $oldName)
                    ->update(['customer_name' => $canonicalName]);
                Vehicle::where('customer_name', $oldName)
                    ->update(['customer_name' => $canonicalName]);

                // Log the merge
                CustomerMergeLog::create([
                    'old_name' => $oldName,
                    'canonical_name' => $canonicalName,
                    'source_type' => $sourceType,
                    'jobs_updated' => $jobsCount,
                    'vehicles_updated' => $vehiclesCount,
                    'merged_by' => auth()->user()?->name ?? 'System',
                    'notes' => "Batch merge from duplicate detection. Source: {$sourceType}",
                ]);

                $totalJobsUpdated += $jobsCount;
                $totalVehiclesUpdated += $vehiclesCount;
            }

            $groupsMerged++;
        }

        return redirect()->route('customers.duplicates')
            ->with('success', "Batch merge complete! Processed {$groupsMerged} groups, updated {$totalJobsUpdated} jobs and {$totalVehiclesUpdated} vehicles. All changes have been logged.");
    }

    /**
     * Detect the source of duplicate customer name
     */
    private function detectDuplicateSource(string $customerName): string
    {
        // Check if jobs with this customer came from invoiced/uninvoiced imports (DMS)
        $dmsImportTypes = ['invoiced', 'uninvoiced'];
        $hasDmsJob = Job::where('customer_name', $customerName)
            ->whereHas('import', function($q) use ($dmsImportTypes) {
                $q->whereIn('import_type', $dmsImportTypes);
            })->exists();

        if ($hasDmsJob) {
            return 'dms_import'; // Need to fix in main DMS system
        }

        // Check if from job progress import
        $hasProgressJob = Job::where('customer_name', $customerName)
            ->whereHas('import', function($q) {
                $q->where('import_type', 'progress');
            })->exists();

        if ($hasProgressJob) {
            return 'job_progress_import'; // User mistake during progress import
        }

        // Manual entry or unknown
        return 'user_entry';
    }

    /**
     * Get human-readable label for source type
     */
    private function getSourceLabel(string $source): string
    {
        return match($source) {
            'dms_import' => 'DMS (Invoice/Uninvoiced)',
            'job_progress_import' => 'Job Progress Import',
            'user_entry' => 'Manual Entry',
            default => $source,
        };
    }

    /**
     * Normalize name for comparison (remove common variations)
     */
    private function normalizeForComparison(string $name): string
    {
        $normalized = strtoupper(trim($name));
        
        // Remove common suffixes/prefixes that vary
        $patterns = [
            '/\bPT\.?\s*/' => 'PT ',
            '/\bCV\.?\s*/' => 'CV ',
            '/\bMR\.?\s*/' => 'MR ',
            '/\bMRS\.?\s*/' => 'MRS ',
            '/\bMS\.?\s*/' => 'MS ',
            '/,\s*/' => ' ',
            '/\s+/' => ' ',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $normalized = preg_replace($pattern, $replacement, $normalized);
        }
        
        return trim($normalized);
    }
}
