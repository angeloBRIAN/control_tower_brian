{{-- Widget: Activity Feed --}}
@props(['activityFeed' => collect()])

<div class="card h-100">
    <div class="card-header-modern">
        <span class="card-header-title">
            <i class="bi bi-activity text-primary"></i>Activity Feed
        </span>
    </div>
    <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
        <div class="timeline ps-3">
            @forelse($activityFeed as $activity)
            <div class="d-flex gap-3 py-3 px-3 border-bottom">
                <div class="timeline-dot">
                    @switch($activity->action ?? 'update')
                        @case('create')
                            <span class="badge bg-success p-2"><i class="bi bi-plus-lg"></i></span>
                            @break
                        @case('update')
                            <span class="badge bg-info p-2"><i class="bi bi-pencil"></i></span>
                            @break
                        @case('status')
                            <span class="badge bg-warning p-2"><i class="bi bi-arrow-repeat"></i></span>
                            @break
                        @case('comment')
                            <span class="badge bg-primary p-2"><i class="bi bi-chat"></i></span>
                            @break
                        @default
                            <span class="badge bg-secondary p-2"><i class="bi bi-dot"></i></span>
                    @endswitch
                </div>
                <div class="flex-grow-1">
                    <div class="small">
                        <strong>{{ $activity->user_name ?? 'System' }}</strong>
                        <span class="text-muted">{{ $activity->description ?? 'performed an action' }}</span>
                    </div>
                    @if(isset($activity->job_number))
                    <a href="{{ route('jobs.show', $activity->job_id) }}" class="small text-primary">
                        {{ $activity->job_number }}
                    </a>
                    @endif
                    <div class="text-muted small mt-1">
                        <i class="bi bi-clock me-1"></i>{{ $activity->created_at?->diffForHumans() ?? '' }}
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-activity display-4 d-block mb-3 opacity-25"></i>
                No recent activity
            </div>
            @endforelse
        </div>
    </div>
</div>
