<?php

namespace App\Http\Controllers;

use App\Models\PdiRecord;
use Illuminate\Http\Request;

class PdiRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = PdiRecord::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('vin', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('pdi_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('pdi_date', '<=', $request->date_to);
        }

        // Sorting
        $sortField = $request->input('sort', 'pdi_date');
        $sortDir = $request->input('dir', 'desc');
        $allowedSorts = ['vin', 'plate_number', 'model', 'pdi_date', 'technician', 'status', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest('pdi_date');
        }

        $perPage = $request->input('per_page', 20);
        $pdis = $query->paginate($perPage)->withQueryString();

        return view('pdi.index', compact('pdis'));
    }

    public function create()
    {
        return view('pdi.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'nullable|string',
            'vin' => 'nullable|string',
            'engine_no' => 'nullable|string',
            'wip' => 'nullable|string',
            'model' => 'nullable|string',
            'colour' => 'nullable|string',
            'pdi_date' => 'required|date',
            'technician' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|string',
        ]);

        PdiRecord::create($validated);

        return redirect()->route('pdi-records.index')->with('success', 'PDI Record created successfully.');
    }

    public function edit(PdiRecord $pdiRecord)
    {
        return view('pdi.form', ['pdi' => $pdiRecord]);
    }

    public function update(Request $request, PdiRecord $pdiRecord)
    {
        $validated = $request->validate([
            'plate_number' => 'nullable|string',
            'vin' => 'nullable|string',
            'engine_no' => 'nullable|string',
            'wip' => 'nullable|string',
            'model' => 'nullable|string',
            'colour' => 'nullable|string',
            'pdi_date' => 'required|date',
            'technician' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $pdiRecord->update($validated);

        return redirect()->route('pdi-records.index')->with('success', 'PDI Record updated successfully.');
    }

    public function destroy(PdiRecord $pdiRecord)
    {
        $pdiRecord->delete();
        return redirect()->route('pdi-records.index')->with('success', 'PDI Record deleted successfully.');
    }
}
