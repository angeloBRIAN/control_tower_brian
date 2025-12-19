<?php

namespace App\Http\Controllers;

use App\Models\Foreman;
use App\Models\User;
use Illuminate\Http\Request;

class ForemanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $foremen = Foreman::with('user')->latest()->paginate(10);
        return view('master.foremen.index', compact('foremen'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('role', 'foreman')->orderBy('name')->get();
        return view('master.foremen.form', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:foremen,name',
            'franchise' => 'nullable|in:PC,CV',
            'active' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
        ]);

        Foreman::create($validated);

        return redirect()->route('foremen.index')
            ->with('success', 'Foreman created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Foreman $foreman)
    {
        $users = User::where('role', 'foreman')->orderBy('name')->get();
        return view('master.foremen.form', compact('foreman', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Foreman $foreman)
    {
        $validated = $request->validate([
             'name' => 'required|string|max:255|unique:foremen,name,' . $foreman->id,
             'franchise' => 'nullable|in:PC,CV',
             'active' => 'boolean',
             'user_id' => 'nullable|exists:users,id',
        ]);

         // Handle checkbox not present
        if (!$request->has('active')) {
            $validated['active'] = false;
        }

        // Handle empty user_id
        if (empty($validated['user_id'])) {
            $validated['user_id'] = null;
        }

        $foreman->update($validated);

        return redirect()->route('foremen.index')
            ->with('success', 'Foreman updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Foreman $foreman)
    {
        $foreman->delete();

        return redirect()->route('foremen.index')
            ->with('success', 'Foreman deleted successfully.');
    }
}

