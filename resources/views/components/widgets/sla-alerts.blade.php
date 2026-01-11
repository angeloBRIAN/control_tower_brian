{{-- Widget: SLA Alerts --}}
@props(['slaAlerts' => collect()])

<div class="card h-100 border-danger">
    <div class="card-header-modern bg-danger-subtle">
        <span class="card-header-title">
            <i class="bi bi-exclamation-triangle-fill text-danger"></i>SLA Alerts
            @if($slaAlerts->count() > 0)
            <span class="badge bg-danger ms-2">{{ $slaAlerts->count() }}</span>
            @endif
        </span>
        <a href="{{ route('jobs.index', ['sla_alert' => 1]) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3">View All</a>
    </div>
    <div class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
        @forelse($slaAlerts as $job)
        <a href="{{ route('jobs.show', $job) }}" class="list-group-item list-group-item-action py-3">
            <div class="d-flex w-100 justify-content-between align-items-start">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fw-bold">{{ $job->job_number }}</span>
                        <span class="badge bg-light text-dark border">{{ $job->plate_number }}</span>
                    </div>
                    <small class="text-muted">{{ $job->customer_name }}</small>
                </div>
                <div class="text-end">
                    @php
                        $daysOld = $job->job_date ? $job->job_date->diffInDays(now()) : 0;
                    @endphp
                    <span class="badge {{ $daysOld > 14 ? 'bg-danger' : 'bg-warning text-dark' }}">
                        {{ $daysOld }} days
                    </span>
                    <div class="small text-muted mt-1">{{ $job->job_date?->format('d M') }}</div>
                </div>
            </div>
        </a>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-check-circle display-4 d-block mb-3 text-success opacity-50"></i>
            <p class="mb-0">All jobs within SLA</p>
        </div>
        @endforelse
    </div>
</div>
