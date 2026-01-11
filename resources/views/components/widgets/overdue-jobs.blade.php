{{-- Widget: Overdue Jobs --}}
@props(['overdueJobs' => collect(), 'thresholdDays' => 14])

<div class="card h-100 border-warning">
    <div class="card-header-modern bg-warning-subtle">
        <span class="card-header-title">
            <i class="bi bi-clock-history text-warning"></i>Overdue Jobs
            @if($overdueJobs->count() > 0)
            <span class="badge bg-warning text-dark ms-2">{{ $overdueJobs->count() }}</span>
            @endif
        </span>
        <a href="{{ route('jobs.index', ['status' => 'uninvoiced', 'min_age' => $thresholdDays]) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Job #</th>
                    <th>Customer</th>
                    <th class="text-end">Age</th>
                </tr>
            </thead>
            <tbody>
                @forelse($overdueJobs->take(5) as $job)
                <tr onclick="window.location='{{ route('jobs.show', $job) }}'" style="cursor: pointer;">
                    <td class="fw-bold text-primary">{{ $job->job_number }}</td>
                    <td class="text-truncate" style="max-width: 120px;">{{ $job->customer_name }}</td>
                    <td class="text-end">
                        @php $days = $job->job_date ? $job->job_date->diffInDays(now()) : 0; @endphp
                        <span class="badge {{ $days > 30 ? 'bg-danger' : 'bg-warning text-dark' }}">
                            {{ $days }}d
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle text-success me-2"></i>No overdue jobs
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
