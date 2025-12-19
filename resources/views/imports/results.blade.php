@extends('layouts.app')

@section('title', 'Import Results')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-check-circle me-2"></i>Import Results</h1>
    <p class="text-muted">{{ $import->file_name }}</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $imported }}</h3>
                <small>New Records</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $updated }}</h3>
                <small>Updated Records</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $failed }}</h3>
                <small>Failed Records</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $jobs->count() }}</h3>
                <small>Total Processed</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2"></i>Imported Data</span>
        <div>
            <a href="{{ route('imports.upload') }}" class="btn btn-sm btn-primary me-2">
                <i class="bi bi-upload me-1"></i>Import More
            </a>
            <a href="{{ route('jobs.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-list me-1"></i>View All Jobs
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Job Number</th>
                        <th>Plate Number</th>
                        <th>Service Advisor</th>
                        <th>Job Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $index => $job)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><a href="{{ route('jobs.show', $job) }}" class="fw-bold">{{ $job->job_number }}</a></td>
                        <td>{{ $job->plate_number ?? '-' }}</td>
                        <td>{{ $job->service_advisor ?? '-' }}</td>
                        <td>{{ $job->job_date?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $job->estimated_amount ? number_format($job->estimated_amount, 0, ',', '.') : '-' }}</td>
                        <td>
                            @if($job->status == 'invoiced')
                                <span class="badge bg-success">Invoiced</span>
                            @else
                                <span class="badge bg-warning text-dark">Uninvoiced</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('jobs.show', $job) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No data imported</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('imports.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Import History
    </a>
</div>
@endsection
