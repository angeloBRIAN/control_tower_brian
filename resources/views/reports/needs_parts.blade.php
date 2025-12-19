@extends('layouts.app')

@section('title', 'Jobs Needing Parts (ORDER)')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-gear me-2"></i>Jobs Needing Parts (ORDER)</h1>
        <p class="text-muted">Uninvoiced jobs with remarks containing "ORDER"</p>
    </div>
    <a href="{{ route('reports.export-needs-parts') }}" class="btn btn-success">
        <i class="bi bi-download me-2"></i>Export to Excel
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search job, plate..." value="{{ request('search') }}">
            </div>
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary me-2"><i class="bi bi-search"></i> Search</button>
                <a href="{{ route('reports.needs-parts') }}" class="btn btn-outline-secondary">Reset</a>
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
                    <td>
                        <span class="badge bg-warning text-dark me-1"><i class="bi bi-gear"></i> ORDER</span>
                        {{ Str::limit($job->latest_remark, 50) }}
                    </td>
                    <td>{{ $job->latest_remark_at?->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No jobs needing parts found</td>
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
