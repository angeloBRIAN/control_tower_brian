@extends('layouts.app')

@section('title', 'Customer - ' . $customerName)

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                <li class="breadcrumb-item active">{{ Str::limit($customerName, 30) }}</li>
            </ol>
        </nav>
        <h1><i class="bi bi-person me-2"></i>{{ $customerName }}</h1>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card text-center border-info">
            <div class="card-body py-3">
                <h3 class="mb-0 text-info">{{ $stats['total_vehicles'] }}</h3>
                <small class="text-muted">Vehicles</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-primary">
            <div class="card-body py-3">
                <h3 class="mb-0 text-primary">{{ $stats['total_jobs'] }}</h3>
                <small class="text-muted">Total Jobs</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-warning">
            <div class="card-body py-3">
                <h3 class="mb-0 text-warning">{{ $stats['uninvoiced_jobs'] }}</h3>
                <small class="text-muted">Uninvoiced</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-success">
            <div class="card-body py-3">
                <h3 class="mb-0 text-success">{{ $stats['invoiced_jobs'] }}</h3>
                <small class="text-muted">Invoiced</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-warning bg-warning bg-opacity-10">
            <div class="card-body py-3">
                <h5 class="mb-0 text-warning">Rp {{ number_format($stats['estimated_sales'] ?? 0, 0, ',', '.') }}</h5>
                <small class="text-muted"><i class="bi bi-hourglass-split me-1"></i>Projected</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-success bg-success bg-opacity-10">
            <div class="card-body py-3">
                <h5 class="mb-0 text-success">Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}</h5>
                <small class="text-muted"><i class="bi bi-check-circle me-1"></i>Invoiced</small>
            </div>
        </div>
    </div>
</div>


<div class="row g-4">
    <!-- Vehicles Card -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-car-front me-2"></i>Vehicles</span>
                <span class="badge bg-info">{{ $vehicles->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($vehicles->count() > 0)
                <ul class="list-group list-group-flush">
                    @foreach($vehicles as $vehicle)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('vehicles.show', $vehicle) }}" class="fw-bold">{{ $vehicle->plate_number }}</a>
                            <br><small class="text-muted">{{ $vehicle->model ?? '-' }}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">{{ $vehicle->jobs_count }} jobs</span>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center text-muted py-3">No vehicles found</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Jobs Card -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-briefcase me-2"></i>Job History</span>
                <span class="badge bg-primary">{{ $stats['total_jobs'] }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>WIP</th>
                                <th>Plate</th>
                                <th>Date</th>
                                <th>SA</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jobs as $job)
                            <tr>
                                <td>
                                    <a href="{{ route('jobs.show', $job) }}" class="fw-bold">{{ $job->job_number }}</a>
                                </td>
                                <td>{{ $job->plate_number }}</td>
                                <td>{{ $job->job_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $job->service_advisor ?? '-' }}</td>
                                <td class="text-end">{{ $job->total_sales ? number_format($job->total_sales, 0, ',', '.') : '-' }}</td>
                                <td>
                                    @if($job->status == 'invoiced')
                                        <span class="badge bg-success">Invoiced</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Uninvoiced</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No jobs found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($jobs->hasPages())
            <div class="card-footer">
                {{ $jobs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
