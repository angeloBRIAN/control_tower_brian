<?php

namespace App\Http\Controllers;

use App\Models\ServiceAdvisor;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceAdvisorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $serviceAdvisors = ServiceAdvisor::with('user')->latest()->paginate(10);
        return view('master.service-advisors.index', compact('serviceAdvisors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('role', 'sa')->orderBy('name')->get();
        return view('master.service-advisors.form', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:service_advisors,name',
            'franchise' => 'nullable|in:PC,CV',
            'active' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
        ]);

        ServiceAdvisor::create($validated);

        return redirect()->route('service-advisors.index')
            ->with('success', 'Service Advisor created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceAdvisor $serviceAdvisor)
    {
        $users = User::where('role', 'sa')->orderBy('name')->get();
        return view('master.service-advisors.form', compact('serviceAdvisor', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceAdvisor $serviceAdvisor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:service_advisors,name,' . $serviceAdvisor->id,
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

        $serviceAdvisor->update($validated);

        return redirect()->route('service-advisors.index')
            ->with('success', 'Service Advisor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceAdvisor $serviceAdvisor)
    {
        $serviceAdvisor->delete();

        return redirect()->route('service-advisors.index')
            ->with('success', 'Service Advisor deleted successfully.');
    }
}

