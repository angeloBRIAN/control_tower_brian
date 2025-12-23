<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerMergeSuggestion;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CustomerMergeController extends Controller
{
    public function index()
    {
        $suggestions = CustomerMergeSuggestion::pending()
            ->orderByDesc('similarity_score')
            ->paginate(25);

        $stats = [
            'pending' => CustomerMergeSuggestion::pending()->count(),
            'merged' => CustomerMergeSuggestion::where('status', 'merged')->count(),
            'ignored' => CustomerMergeSuggestion::where('status', 'ignored')->count(),
        ];

        return view('admin.customer-merge.index', compact('suggestions', 'stats'));
    }

    /**
     * Refresh suggestions (re-run duplicate detection)
     */
    public function refresh()
    {
        Artisan::call('customers:find-duplicates', ['--force' => true]);
        $output = Artisan::output();

        return redirect()->route('admin.customer-merge.index')
            ->with('success', 'Suggestions refreshed. ' . trim($output));
    }

    /**
     * Merge customer names (update all jobs with name B to use name A)
     */
    public function merge(CustomerMergeSuggestion $suggestion)
    {
        $nameA = $suggestion->customer_name_a;
        $nameB = $suggestion->customer_name_b;

        // Update all jobs with name_b to use name_a
        $updated = Job::where('customer_name', $nameB)->update(['customer_name' => $nameA]);

        // Mark as merged
        $suggestion->update(['status' => 'merged']);

        return redirect()->route('admin.customer-merge.index')
            ->with('success', "Merged '{$nameB}' into '{$nameA}'. {$updated} job(s) updated.");
    }

    /**
     * Ignore this suggestion
     */
    public function ignore(CustomerMergeSuggestion $suggestion)
    {
        $suggestion->update(['status' => 'ignored']);

        return redirect()->route('admin.customer-merge.index')
            ->with('success', 'Suggestion ignored.');
    }

    /**
     * Clear all ignored suggestions
     */
    public function clearIgnored()
    {
        $count = CustomerMergeSuggestion::where('status', 'ignored')->delete();

        return redirect()->route('admin.customer-merge.index')
            ->with('success', "{$count} ignored suggestion(s) cleared.");
    }
}
