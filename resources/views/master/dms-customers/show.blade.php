@extends('layouts.app')

@section('title', 'Customer: ' . ($customer->name ?: 'No Name'))

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('dms-customers.index') }}">DMS Customers</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($customer->name ?: 'No Name', 30) }}</li>
        </ol>
    </nav>
    <h1><i class="bi bi-person me-2"></i>{{ $customer->name ?: 'No Name' }}</h1>
</div>

<div class="row">
    <!-- Customer Info -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Customer Information
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th class="w-40">DMS Magic</th>
                        <td>{{ $customer->dms_magic ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $customer->name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Company</th>
                        <td>{{ $customer->company_name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Department</th>
                        <td>{{ $customer->department ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $customer->email ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $customer->phone ?: $customer->phone_1 ?: '-' }}</td>
                    </tr>
                    @if($customer->address)
                    <tr>
                        <th>Address</th>
                        <td>{{ $customer->address }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>DMS Created</th>
                        <td>{{ $customer->dms_created_at ? \Carbon\Carbon::parse($customer->dms_created_at)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Imported</th>
                        <td>{{ $customer->dms_imported_at ? \Carbon\Carbon::parse($customer->dms_imported_at)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Aliases -->
        @if($customer->aliases->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-link-45deg me-2"></i>Name Aliases
            </div>
            <div class="card-body">
                @foreach($customer->aliases as $alias)
                <span class="badge bg-secondary me-1 mb-1">{{ $alias->alias_name }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Vehicles -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <span><i class="bi bi-truck me-2"></i>Vehicles</span>
                <span class="badge bg-info">{{ $customer->linkedVehicles->count() }}</span>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                @if($customer->linkedVehicles->count() > 0)
                <table class="table table-sm mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Plate</th>
                            <th>Model</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->linkedVehicles as $vehicle)
                        <tr>
                            <td>
                                <a href="{{ route('vehicles.show', $vehicle) }}">{{ $vehicle->plate_number }}</a>
                            </td>
                            <td>{{ Str::limit($vehicle->model, 15) }}</td>
                            <td>
                                @if($vehicle->is_in_workshop)
                                <span class="badge bg-warning text-dark">In Workshop</span>
                                @else
                                <span class="badge bg-secondary">Out</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-truck display-6"></i>
                    <p class="mt-2 mb-0">No vehicles linked</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Jobs -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <span><i class="bi bi-wrench me-2"></i>Recent Jobs</span>
                <span class="badge bg-primary">{{ $customer->linkedJobs->count() }}</span>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                @if($customer->linkedJobs->count() > 0)
                <table class="table table-sm mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Job #</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->linkedJobs as $job)
                        <tr>
                            <td>
                                <a href="{{ route('jobs.show', $job) }}">{{ $job->job_number ?: '-' }}</a>
                            </td>
                            <td>{{ $job->job_date ? $job->job_date->format('d/m/y') : '-' }}</td>
                            <td>
                                @if($job->status === 'invoiced')
                                <span class="badge bg-success">Invoiced</span>
                                @else
                                <span class="badge bg-warning text-dark">Uninvoiced</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-wrench display-6"></i>
                    <p class="mt-2 mb-0">No jobs linked</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
