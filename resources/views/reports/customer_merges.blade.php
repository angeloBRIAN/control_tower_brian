@extends('layouts.app')

@section('title', 'Customer Merge Report')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Customer Merge Report</li>
            </ol>
        </nav>
        <h1><i class="bi bi-file-earmark-text me-2"></i>Customer Merge Report</h1>
        <p class="text-muted mb-0">History of customer name merges for DMS data sanitization</p>
    </div>
    <div class="d-flex gap-2">
        <div class="btn-group">
            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-download me-1"></i>Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('reports.customer-merges.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}">
                        <i class="bi bi-file-earmark-excel text-success me-2"></i>Export to Excel
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('reports.customer-merges.export', array_merge(request()->query(), ['format' => 'csv'])) }}">
                        <i class="bi bi-filetype-csv text-primary me-2"></i>Export to CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('reports.customer-merges.export', array_merge(request()->query(), ['format' => 'pdf'])) }}">
                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>Export to PDF
                    </a>
                </li>
            </ul>
        </div>
        <a href="{{ route('customers.duplicates') }}" class="btn btn-warning">
            <i class="bi bi-people me-1"></i>Merge Duplicates
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <h4 class="mb-0 text-primary">{{ $stats['total'] }}</h4>
                <small class="text-muted">Total Merges</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-danger bg-danger bg-opacity-10">
            <div class="card-body py-3">
                <h4 class="mb-0 text-danger">{{ $stats['dms_issues'] }}</h4>
                <small class="text-muted">DMS Issues</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-warning bg-warning bg-opacity-10">
            <div class="card-body py-3">
                <h4 class="mb-0 text-warning">{{ $stats['user_mistakes'] }}</h4>
                <small class="text-muted">User Mistakes</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-success">
            <div class="card-body py-3">
                <h4 class="mb-0 text-success">{{ number_format($stats['jobs_fixed']) }}</h4>
                <small class="text-muted">Jobs Fixed</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center border-info">
            <div class="card-body py-3">
                <h4 class="mb-0 text-info">{{ number_format($stats['vehicles_fixed']) }}</h4>
                <small class="text-muted">Vehicles Fixed</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Source Type</label>
                <select name="source_type" class="form-select form-select-sm">
                    <option value="">All Sources</option>
                    <option value="dms_import" {{ request('source_type') == 'dms_import' ? 'selected' : '' }}>DMS Import (Invoice/Uninvoiced)</option>
                    <option value="job_progress_import" {{ request('source_type') == 'job_progress_import' ? 'selected' : '' }}>Job Progress Import</option>
                    <option value="user_entry" {{ request('source_type') == 'user_entry' ? 'selected' : '' }}>Manual Entry</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">From Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">To Date</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('reports.customer-merges') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Old Name</th>
                        <th>Merged To</th>
                        <th class="text-center">Source</th>
                        <th class="text-center">Jobs</th>
                        <th class="text-center">Vehicles</th>
                        <th>Merged By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    @php
                        $sourceBadge = match($log->source_type) {
                            'dms_import' => 'bg-danger',
                            'job_progress_import' => 'bg-warning text-dark',
                            'user_entry' => 'bg-secondary',
                            default => 'bg-secondary',
                        };
                    @endphp
                    <tr>
                        <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <del class="text-muted">{{ $log->old_name }}</del>
                        </td>
                        <td class="fw-bold text-success">
                            <i class="bi bi-arrow-right me-1"></i>{{ $log->canonical_name }}
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $sourceBadge }}">{{ $log->source_type_label }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $log->jobs_updated }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ $log->vehicles_updated }}</span>
                        </td>
                        <td>{{ $log->merged_by }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No merge logs found. <a href="{{ route('customers.duplicates') }}">Merge some duplicates</a> to see data here.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer">
        {{ $logs->links() }}
    </div>
    @endif
</div>

<!-- DMS Export Note -->
<div class="alert alert-info mt-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>For DMS Sanitization:</strong> Filter by "DMS Import" source type to see duplicates that originated from your main DMS system.
    Use this data to clean up customer records in your DMS to prevent future duplicates.
</div>
@endsection
