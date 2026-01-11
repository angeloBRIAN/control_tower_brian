{{-- Widget: Announcements --}}
@props(['announcements' => null])

@php
    // Load announcements if not passed
    if ($announcements === null && auth()->check()) {
        $announcements = \App\Models\Announcement::getForUser(auth()->user(), 5);
    } else {
        $announcements = collect($announcements ?? []);
    }
@endphp

<div class="card h-100 border-info">
    <div class="card-header-modern bg-info-subtle">
        <span class="card-header-title">
            <i class="bi bi-megaphone-fill text-info"></i>Announcements
            @if($announcements->count() > 0)
            <span class="badge bg-info ms-2">{{ $announcements->count() }}</span>
            @endif
        </span>
    </div>
    <div class="list-group list-group-flush" style="max-height: 320px; overflow-y: auto;">
        @forelse($announcements as $announcement)
        <div class="list-group-item py-3 {{ $announcement->is_important ? 'border-start border-danger border-3' : '' }}" 
             id="announcement-{{ $announcement->id }}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-2">
                    @if($announcement->is_pinned)
                    <i class="bi bi-pin-fill text-primary" title="Pinned"></i>
                    @endif
                    @if($announcement->is_important)
                    <span class="badge bg-danger">Important</span>
                    @endif
                    <strong class="text-info">{{ $announcement->title }}</strong>
                </div>
                <button type="button" class="btn btn-sm btn-link text-muted p-0" 
                        onclick="dismissAnnouncement({{ $announcement->id }})"
                        title="Dismiss">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="small announcement-content mb-2">
                {!! Str::limit(strip_tags($announcement->content), 150) !!}
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="bi bi-person me-1"></i>{{ $announcement->author->name ?? 'Admin' }}
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

@push('scripts')
<script>
function dismissAnnouncement(id) {
    fetch(`/announcements/${id}/dismiss`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const element = document.getElementById(`announcement-${id}`);
            if (element) {
                element.style.transition = 'opacity 0.3s, transform 0.3s';
                element.style.opacity = '0';
                element.style.transform = 'translateX(20px)';
                setTimeout(() => element.remove(), 300);
            }
        }
    })
    .catch(err => console.error('Failed to dismiss:', err));
}
</script>
@endpush
