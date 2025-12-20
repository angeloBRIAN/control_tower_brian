<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class WipConflictReportController extends Controller
{
    /**
     * Display WIP Conflict/Dummy Report
     */
    public function index(Request $request)
    {
        $query = Job::where('is_dummy_wip', true);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Franchise filter
        if ($request->filled('franchise')) {
            $query->where('franchise', $request->franchise);
        }

        // Type filter (DUP vs WRONG)
        if ($request->filled('type')) {
            if ($request->type === 'dup') {
                $query->where('job_number', 'like', '%-DUP-%');
            } elseif ($request->type === 'wrong') {
                $query->where('job_number', 'like', '%-WRONG-%');
            }
        }

        $conflicts = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        // Stats
        $stats = [
            'total' => Job::where('is_dummy_wip', true)->count(),
            'dup' => Job::where('is_dummy_wip', true)->where('job_number', 'like', '%-DUP-%')->count(),
            'wrong' => Job::where('is_dummy_wip', true)->where('job_number', 'like', '%-WRONG-%')->count(),
        ];

        return view('reports.wip-conflicts', compact('conflicts', 'stats'));
    }

    /**
     * Manually resolve a conflict by updating WIP
     */
    public function resolve(Request $request, Job $job)
    {
        $request->validate([
            'new_wip' => 'required|string|max:50',
        ]);

        $oldWip = $job->job_number;
        $newWip = $request->input('new_wip');

        // Check if new WIP already exists
        $existing = Job::where('job_number', $newWip)->first();
        if ($existing) {
            return redirect()->back()->with('error', "WIP {$newWip} already exists (Plate: {$existing->plate_number}). Cannot resolve.");
        }

        $job->update([
            'job_number' => $newWip,
            'is_dummy_wip' => false,
            'description' => ($job->description ?? '') . " [MANUAL FIX: Changed from {$oldWip} to {$newWip}]"
        ]);

        return redirect()->back()->with('success', "Job {$oldWip} resolved to {$newWip}");
    }
}
