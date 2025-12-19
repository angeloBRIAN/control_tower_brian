<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('wip', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('booking_date', '<=', $request->date_to);
        }

        // Sorting
        $sortField = $request->input('sort', 'booking_date');
        $sortDir = $request->input('dir', 'desc');
        $allowedSorts = ['wip', 'plate_number', 'customer_name', 'booking_date', 'service_type', 'foreman', 'service_advisor', 'status', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest('booking_date');
        }

        $perPage = $request->input('per_page', 20);
        $bookings = $query->paginate($perPage)->withQueryString();

        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        return view('bookings.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_number' => 'nullable|string',
            'wip' => 'nullable|string',
            'customer_name' => 'nullable|string',
            'booking_date' => 'required|date',
            'service_type' => 'nullable|string',
            'foreman' => 'nullable|string',
            'service_advisor' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|string',
        ]);

        Booking::create($validated);

        return redirect()->route('bookings.index')->with('success', 'Booking created successfully.');
    }

    public function edit(Booking $booking)
    {
        return view('bookings.form', compact('booking'));
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'plate_number' => 'nullable|string',
            'wip' => 'nullable|string',
            'customer_name' => 'nullable|string',
            'booking_date' => 'required|date',
            'service_type' => 'nullable|string',
            'foreman' => 'nullable|string',
            'service_advisor' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $booking->update($validated);

        return redirect()->route('bookings.index')->with('success', 'Booking updated successfully.');
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return redirect()->route('bookings.index')->with('success', 'Booking deleted successfully.');
    }
}
