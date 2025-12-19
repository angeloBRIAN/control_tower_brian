@extends('layouts.app')

@section('title', 'Uninvoiced Jobs Report')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-exclamation-triangle me-2"></i>Uninvoiced Jobs</h1>
        <p class="text-muted">Jobs that haven't been invoiced yet</p>
    </div>
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-download me-2"></i>Export Report
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="{{ route('reports.export-uninvoiced') }}"><i class="bi bi-file-earmark-excel text-success me-2"></i>Export to Excel</a></li>
            <li><a class="dropdown-item disabled" href="#"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Export to PDF (Coming Soon)</a></li>
        </ul>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search job, plate, remark..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" placeholder="From" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" placeholder="To" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary me-2"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('reports.uninvoiced') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Job #</th>
                    <th>Plate</th>
                    <th>SA</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Work Status</th>
                    <th>Last Remark</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobs as $job)
                <tr>
                    <td><a href="{{ route('jobs.show', $job) }}" class="fw-bold">{{ $job->job_number }}</a></td>
                    <td>{{ $job->plate_number }}</td>
                    <td>{{ $job->service_advisor ?? '-' }}</td>
                    <td>{{ $job->job_date?->format('d/m/Y') }}</td>
                    <td>{{ $job->estimated_amount ? number_format($job->estimated_amount, 0, ',', '.') : '-' }}</td>
                    <td><span class="badge bg-secondary">{{ $job->work_status ?? 'Pending' }}</span></td>
                    <td class="text-truncate" style="max-width: 200px;">
                        @if($job->latest_remark && stripos($job->latest_remark, 'ORDER') !== false)
                            <span class="badge bg-warning text-dark me-1"><i class="bi bi-gear"></i></span>
                        @endif
                        {{ $job->latest_remark }}
                    </td>
                    <td>{{ $job->latest_remark_at?->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No uninvoiced jobs found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $jobs->withQueryString()->links() }}
</div>
@endsection
