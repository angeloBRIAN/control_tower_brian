@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Hero Welcome Section -->
<div class="hero-welcome">
    <div class="row align-items-center position-relative" style="z-index: 1;">
        <div class="col-md-8">
            <h1>Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="mb-0 opacity-75 lead">Here's what's happening in the workshop today.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="badge bg-white text-dark px-3 py-2 rounded-pill shadow-sm">
                <i class="bi bi-calendar3 me-2"></i>{{ now()->format('l, d F Y') }}
            </div>
        </div>
    </div>
</div>

@php
    $uninvoicedCount = \App\Models\Job::uninvoiced()->count();
    $invoicedCount = \App\Models\Job::invoiced()->count();
    $needsPartsCount = \App\Models\Job::uninvoiced()->needsParts()->count();
    $vehiclesInWorkshop = \App\Models\Vehicle::where('is_in_workshop', true)->count();
    
    // Count duplicate customer name groups for alert
    $duplicateCustomerCount = 0;
    try {
        $allNames = \Illuminate\Support\Facades\DB::table(
            \Illuminate\Support\Facades\DB::raw("(
                SELECT DISTINCT customer_name as name FROM vehicles WHERE customer_name IS NOT NULL AND customer_name != ''
                UNION
                SELECT DISTINCT customer_name as name FROM jobs WHERE customer_name IS NOT NULL AND customer_name != ''
            ) as customers")
        )->pluck('name')->toArray();
        
        // Simple check for similar names (first 10 chars match with different endings)
        $checked = [];
        foreach ($allNames as $name1) {
            if (in_array($name1, $checked)) continue;
            $prefix = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $name1), 0, 15));
            if (strlen($prefix) < 10) continue;
            foreach ($allNames as $name2) {
                if ($name1 === $name2 || in_array($name2, $checked)) continue;
                $prefix2 = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $name2), 0, 15));
                if ($prefix === $prefix2) {
                    $duplicateCustomerCount++;
                    $checked[] = $name2;
                }
            }
            $checked[] = $name1;
        }
    } catch (\Exception $e) {
        $duplicateCustomerCount = 0;
    }
@endphp

@if($duplicateCustomerCount > 0)
<div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
    <div class="flex-grow-1">
        <strong>Duplicate Customer Names Detected!</strong>
        Found approximately <strong>{{ $duplicateCustomerCount }}</strong> potential duplicate customer names that may need merging.
        This could indicate data issues in your DMS system.
    </div>
    <a href="{{ route('customers.duplicates') }}" class="btn btn-warning btn-sm ms-3">
        <i class="bi bi-arrow-right-circle me-1"></i>Review & Merge
    </a>
</div>
@endif

<!-- Stat Cards -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="stat-card-modern">
            <p class="stat-value">{{ $uninvoicedCount }}</p>
            <p class="stat-label mb-0"><i class="bi bi-clock me-1"></i>Uninvoiced Jobs</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-modern warning">
            <p class="stat-value">{{ $needsPartsCount }}</p>
            <p class="stat-label mb-0"><i class="bi bi-gear me-1"></i>Needs Parts</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-modern success">
            <p class="stat-value">{{ $invoicedCount }}</p>
            <p class="stat-label mb-0"><i class="bi bi-check-circle me-1"></i>Invoiced Jobs</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-modern info">
            <p class="stat-value">{{ $vehiclesInWorkshop }}</p>
            <p class="stat-label mb-0"><i class="bi bi-car-front me-1"></i>In Workshop</p>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="row g-4 mb-5">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header-modern">
                <span class="card-header-title">
                    <i class="bi bi-exclamation-triangle text-warning"></i>Recent Open Jobs
                </span>
                <a href="{{ route('jobs.index', ['status' => 'uninvoiced']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-modern mb-0 table-hover">
                    <thead>
                        <tr>
                            <th>Job #</th>
                            <th>Plate No</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(\App\Models\Job::uninvoiced()->latest()->take(5)->get() as $job)
                        <tr onclick="window.location='{{ route('jobs.show', $job) }}'" style="cursor: pointer;">
                            <td class="fw-bold text-primary">{{ $job->job_number }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $job->plate_number }}</span></td>
                            <td class="text-truncate" style="max-width: 150px;">{{ $job->customer_name }}</td>
                            <td>{{ $job->job_date?->format('d M') }}</td>
                            <td><span class="badge bg-warning text-dark">{{ $job->work_status ?? 'Pending' }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-check2-circle display-4 d-block mb-3 opacity-25"></i>
                                No uninvoiced jobs found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header-modern">
                <span class="card-header-title">
                    <i class="bi bi-tools text-danger"></i>Needs Parts
                </span>
                <a href="{{ route('jobs.index', ['need_part' => 1]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">View All</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse(\App\Models\Job::uninvoiced()->needsParts()->latest()->take(5)->get() as $job)
                <a href="{{ route('jobs.show', $job) }}" class="list-group-item list-group-item-action py-3">
                    <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                        <h6 class="mb-0 fw-bold">{{ $job->plate_number }}</h6>
                        <small class="text-muted">{{ $job->job_number }}</small>
                    </div>
                    <p class="mb-1 small text-muted text-truncate">{{ $job->latest_remark }}</p>
                    <small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Parts Required</small>
                </a>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-check2-all display-4 d-block mb-3 opacity-25"></i>
                    All clear
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mb-4">
    <h5 class="mb-4 fw-bold text-muted text-uppercase small ls-1">Quick Actions</h5>
    <div class="row g-4">
        <div class="col-md-3">
            <a href="{{ route('jobs.create') }}" class="action-card">
                <div class="action-icon-wrapper">
                    <i class="bi bi-plus-lg"></i>
                </div>
                <div class="action-title">New Job</div>
                <div class="action-desc">Create a new job order</div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('imports.upload') }}" class="action-card">
                <div class="action-icon-wrapper">
                    <i class="bi bi-cloud-upload"></i>
                </div>
                <div class="action-title">Import Data</div>
                <div class="action-desc">Upload Excel/ODS files</div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('reports.export-uninvoiced') }}" class="action-card">
                <div class="action-icon-wrapper">
                    <i class="bi bi-file-earmark-arrow-down"></i>
                </div>
                <div class="action-title">Export Report</div>
                <div class="action-desc">Download uninvoiced jobs</div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('reports.needs-parts') }}" class="action-card">
                <div class="action-icon-wrapper">
                    <i class="bi bi-gear-wide-connected"></i>
                </div>
                <div class="action-title">Parts Report</div>
                <div class="action-desc">View parts requirements</div>
            </a>
        </div>
    </div>
</div>
@endsection
