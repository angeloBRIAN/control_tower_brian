{{-- Widget: Team Workload --}}
@props(['teamWorkload' => []])

<div class="card h-100">
    <div class="card-header-modern">
        <span class="card-header-title">
            <i class="bi bi-people-fill text-primary"></i>Team Workload
        </span>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($teamWorkload as $member)
            <div class="list-group-item py-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-circle bg-primary text-white" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">
                            {{ strtoupper(substr($member['name'] ?? 'N', 0, 2)) }}
                        </div>
                        <span class="fw-semibold">{{ Str::limit($member['name'] ?? 'Unknown', 15) }}</span>
                    </div>
                    <span class="badge {{ ($member['jobs'] ?? 0) > 5 ? 'bg-danger' : (($member['jobs'] ?? 0) > 3 ? 'bg-warning' : 'bg-success') }}">
                        {{ $member['jobs'] ?? 0 }} jobs
                    </span>
                </div>
                <div class="progress" style="height: 6px;">
                    @php $loadPercent = min(100, (($member['jobs'] ?? 0) / 8) * 100); @endphp
                    <div class="progress-bar {{ $loadPercent > 75 ? 'bg-danger' : ($loadPercent > 50 ? 'bg-warning' : 'bg-success') }}" 
                         style="width: {{ $loadPercent }}%"></div>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people display-4 d-block mb-3 opacity-25"></i>
                No team data available
            </div>
            @endforelse
        </div>
    </div>
</div>
