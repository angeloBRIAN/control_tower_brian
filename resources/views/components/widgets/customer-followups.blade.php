{{-- Widget: Customer Follow-ups --}}
@props(['followups' => collect()])

<div class="card h-100">
    <div class="card-header-modern">
        <span class="card-header-title">
            <i class="bi bi-telephone-outbound-fill text-info"></i>Customer Follow-ups
        </span>
        <span class="badge bg-info">{{ $followups->count() }} pending</span>
    </div>
    <div class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
        @forelse($followups as $job)
        <a href="{{ route('jobs.show', $job) }}" class="list-group-item list-group-item-action py-3">
            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                <strong>{{ $job->customer_name }}</strong>
                <small class="text-muted">{{ $job->plate_number }}</small>
            </div>
            <p class="mb-1 small text-muted">{{ Str::limit($job->latest_remark ?? 'No remarks', 60) }}</p>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Job: {{ $job->job_number }}</small>
                @php $daysWaiting = $job->updated_at ? $job->updated_at->diffInDays(now()) : 0; @endphp
                @if($daysWaiting > 3)
                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>{{ $daysWaiting }}d waiting</span>
                @else
                <span class="badge bg-light text-dark border">{{ $daysWaiting }}d ago</span>
                @endif
            </div>
        </a>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-check-circle display-4 d-block mb-3 opacity-25"></i>
            No follow-ups needed
        </div>
        @endforelse
    </div>
</div>
