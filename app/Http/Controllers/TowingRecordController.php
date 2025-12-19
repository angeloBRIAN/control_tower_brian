<?php

namespace App\Http\Controllers;

use App\Models\TowingRecord;
use Illuminate\Http\Request;

class TowingRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = TowingRecord::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('pickup_location', 'like', "%{$search}%");
            });
        }

        // Job type filter
        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }

        // Sorting
        $sortField = $request->input('sort', 'scheduled_date');
        $sortDir = $request->input('dir', 'desc');
        $allowedSorts = ['plate_number', 'customer_name', 'scheduled_date', 'pickup_location', 'job_type', 'status', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest('scheduled_date');
        }

        $perPage = $request->input('per_page', 20);
        $towings = $query->paginate($perPage)->withQueryString();

        return view('towing.index', compact('towings'));
    }

    public function create()
    {
        return view('towing.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string',
            'scheduled_date' => 'required|date',
            'job_type' => 'required|in:towing,storing',
            'status' => 'required|string',
            'pickup_location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        TowingRecord::create($validated);

        return redirect()->route('towing-records.index')->with('success', 'Towing Record created successfully.');
    }

    public function edit(TowingRecord $towingRecord)
    {
        return view('towing.form', ['towing' => $towingRecord]);
    }

    public function update(Request $request, TowingRecord $towingRecord)
    {
        $validated = $request->validate([
            'plate_number' => 'required|string',
            'scheduled_date' => 'required|date',
            'job_type' => 'required|in:towing,storing',
            'status' => 'required|string',
            'pickup_location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $towingRecord->update($validated);

        return redirect()->route('towing-records.index')->with('success', 'Towing Record updated successfully.');
    }

    public function destroy(TowingRecord $towingRecord)
    {
        $towingRecord->delete();
        return redirect()->route('towing-records.index')->with('success', 'Towing Record deleted successfully.');
    }
}
