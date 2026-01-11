{{-- Widget: Announcements --}}
@props(['announcements' => collect()])

<div class="card h-100 border-info">
    <div class="card-header-modern bg-info-subtle">
        <span class="card-header-title">
            <i class="bi bi-megaphone-fill text-info"></i>Announcements
        </span>
    </div>
    <div class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
        @forelse($announcements as $announcement)
        <div class="list-group-item py-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <strong class="text-info">{{ $announcement->title ?? 'Announcement' }}</strong>
                @if($announcement->is_important ?? false)
                <span class="badge bg-danger"><i class="bi bi-exclamation-circle"></i></span>
                @endif
            </div>
            <p class="mb-2 small">{{ Str::limit($announcement->content ?? '', 120) }}</p>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="bi bi-person me-1"></i>{{ $announcement->author ?? 'Admin' }}
                </small>
                <small class="text-muted">{{ $announcement->created_at?->diffForHumans() ?? '' }}</small>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-megaphone display-4 d-block mb-3 opacity-25"></i>
            No announcements
        </div>
        @endforelse
    </div>
</div>
