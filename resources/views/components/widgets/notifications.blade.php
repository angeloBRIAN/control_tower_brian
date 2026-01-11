{{-- Widget: Notifications --}}
@props(['notifications' => collect()])

<div class="card h-100">
    <div class="card-header-modern">
        <span class="card-header-title">
            <i class="bi bi-bell-fill text-warning"></i>Notifications
            @if($notifications->count() > 0)
            <span class="badge bg-danger ms-2">{{ $notifications->count() }}</span>
            @endif
        </span>
        <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">View All</a>
    </div>
    <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
        @forelse($notifications->take(5) as $notification)
        <div class="list-group-item py-3 {{ $notification->read_at ? '' : 'bg-light' }}">
            <div class="d-flex w-100 justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        @if(!$notification->read_at)
                        <span class="badge bg-primary" style="width: 8px; height: 8px; padding: 0; border-radius: 50%;"></span>
                        @endif
                        <strong class="small">{{ $notification->title ?? 'Notification' }}</strong>
                    </div>
                    <p class="mb-1 small text-muted">{{ Str::limit($notification->message ?? '', 80) }}</p>
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                </div>
                @if(!$notification->read_at)
                <form action="{{ route('notifications.read', $notification) }}" method="POST" class="ms-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-link text-muted p-0" title="Mark as read">
                        <i class="bi bi-check2"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bell-slash display-4 d-block mb-3 opacity-25"></i>
            No notifications
        </div>
        @endforelse
    </div>
</div>
