@extends('layouts.app')

@section('title', 'Import Details')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('imports.index') }}">Import History</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </nav>
        <h1><i class="bi bi-file-earmark-spreadsheet me-2"></i>{{ $import->file_name }}</h1>
    </div>
</div>

<!-- Summary Card -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <h4 class="mb-0 text-primary">{{ $import->import_type }}</h4>
                <small class="text-muted">Type</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <h4 class="mb-0 text-success">{{ number_format($import->records_imported) }}</h4>
                <small class="text-muted">New Records</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <h4 class="mb-0 text-info">{{ number_format($import->records_updated) }}</h4>
                <small class="text-muted">Updated</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <h4 class="mb-0 text-danger">{{ number_format($import->records_failed) }}</h4>
                <small class="text-muted">Failed</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body py-3">
                <div class="row text-center">
                    <div class="col-6">
                        <small class="text-muted d-block">Imported By</small>
                        <strong>{{ $import->imported_by ?? 'System' }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Date</small>
                        <strong>{{ $import->created_at->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dummy WIP Jobs (Conflict Report) -->
@if($dummyJobs->count() > 0)
<div class="card mb-4">
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <span><i class="bi bi-exclamation-diamond me-2"></i>WIP Conflict Report</span>
        <span class="badge bg-dark">{{ $dummyJobs->count() }} conflicts found</span>
    </div>
    <div class="card-body p-0">
        <div class="alert alert-info m-3 mb-0">
            <i class="bi bi-info-circle me-2"></i>
            <strong>What is this?</strong> These jobs were created with temporary WIP numbers because the original WIP was already assigned to a different vehicle. 
            When you import the correct Invoice/Uninvoiced report, these will be automatically fixed with the correct WIP number.
        </div>
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Temp WIP</th>
                        <th>Original WIP</th>
                        <th>Plate Number</th>
                        <th>Customer</th>
                        <th>Job Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dummyJobs as $job)
                    @php
                        // Extract original WIP from temp WIP (e.g. "24298-DUP-123" -> "24298")
                        $originalWip = preg_replace('/-DUP-\d+$/', '', $job->job_number);
                    @endphp
                    <tr>
                        <td><code class="text-warning">{{ $job->job_number }}</code></td>
                        <td><code>{{ $originalWip }}</code></td>
                        <td><strong>{{ $job->plate_number ?? '-' }}</strong></td>
                        <td>{{ Str::limit($job->customer_name, 30) ?? '-' }}</td>
                        <td>{{ $job->job_date ? $job->job_date->format('d/m/Y') : '-' }}</td>
                        <td>
                            @if($job->is_dummy_wip)
                                <span class="badge bg-warning text-dark">Pending Fix</span>
                            @else
                                <span class="badge bg-success">Resolved</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('jobs.show', $job) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endif

<!-- WIP Swaps and Plate Corrections from this Import -->
@if($import->conflict_rows && count($import->conflict_rows) > 0)
<div class="card mb-4">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-arrow-left-right me-2"></i>Data Corrections</span>
        <span class="badge bg-light text-info">{{ count($import->conflict_rows) }} corrections</span>
    </div>
    <div class="card-body p-0">
        <div class="alert alert-info m-3 mb-0">
            <i class="bi bi-info-circle me-2"></i>
            <strong>What is this?</strong> These records show plate number corrections or WIP swaps performed during the import.
        </div>
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Row</th>
                        <th>Type</th>
                        <th>WIP</th>
                        <th>Old Plate</th>
                        <th>New Plate</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($import->conflict_rows as $conflict)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $conflict['row'] ?? 'N/A' }}</span></td>
                        <td>
                            @if(($conflict['type'] ?? '') === 'PLATE_CORRECTION')
                                <span class="badge bg-success">Plate Fix</span>
                            @else
                                <span class="badge bg-warning text-dark">WIP Swap</span>
                            @endif
                        </td>
                        <td><code>{{ $conflict['original_wip'] ?? 'N/A' }}</code></td>
                        <td><code class="text-danger">{{ $conflict['demoted_plate'] ?? '-' }}</code></td>
                        <td><strong class="text-success">{{ $conflict['promoted_plate'] ?? '-' }}</strong></td>
                        <td><small>{{ $conflict['action'] ?? '-' }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($import->records_failed > 0 && $import->failed_rows)
<div class="card">
    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-exclamation-triangle me-2"></i>Failed Rows Details</span>
        <span class="badge bg-light text-danger">{{ count($import->failed_rows) }} of {{ $import->records_failed }} shown</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Row #</th>
                        <th>Sheet</th>
                        <th>Job Number</th>
                        <th>Plate Number</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($import->failed_rows as $row)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $row['row'] ?? 'N/A' }}</span></td>
                        <td>{{ $row['sheet'] ?? 'N/A' }}</td>
                        <td><code>{{ $row['job_number'] ?? 'N/A' }}</code></td>
                        <td><code>{{ $row['plate_number'] ?? 'N/A' }}</code></td>
                        <td><small class="text-danger">{{ $row['error'] ?? 'Unknown error' }}</small></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if($import->records_failed > count($import->failed_rows))
    <div class="card-footer text-muted text-center">
        <small>Showing first {{ count($import->failed_rows) }} failed rows. {{ $import->records_failed - count($import->failed_rows) }} more not shown.</small>
    </div>
    @endif
</div>
@elseif($import->records_failed > 0)
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    {{ $import->records_failed }} records failed, but detailed error information is not available (imported before this feature was added).
</div>
@else
<div class="alert alert-success">
    <i class="bi bi-check-circle me-2"></i>
    All records were imported successfully!
</div>
@endif

<div class="mt-3">
    <a href="{{ route('imports.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Import History
    </a>
</div>
@endsection
