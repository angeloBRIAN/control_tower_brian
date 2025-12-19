@extends('layouts.app')

@section('title', 'Invoiced Jobs Report')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-check-circle me-2"></i>Invoiced Jobs</h1>
    <p class="text-muted">Jobs that have been invoiced</p>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search job, plate, invoice..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" placeholder="From" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" placeholder="To" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary me-2"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('reports.invoiced') }}" class="btn btn-outline-secondary">Reset</a>
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
                    <th>Job Date</th>
                    <th>Invoice #</th>
                    <th>Invoiced Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobs as $job)
                <tr>
                    <td><a href="{{ route('jobs.show', $job) }}" class="fw-bold">{{ $job->job_number }}</a></td>
                    <td>{{ $job->plate_number }}</td>
                    <td>{{ $job->service_advisor ?? '-' }}</td>
                    <td>{{ $job->job_date?->format('d/m/Y') }}</td>
                    <td><span class="badge bg-success">{{ $job->invoice_number }}</span></td>
                    <td>{{ $job->invoiced_at?->format('d/m/Y') }}</td>
                    <td>{{ $job->estimated_amount ? number_format($job->estimated_amount, 0, ',', '.') : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No invoiced jobs found</td>
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
