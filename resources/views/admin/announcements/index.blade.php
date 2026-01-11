@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1"><i class="bi bi-megaphone-fill me-2"></i>Announcements</h2>
        <p class="text-muted mb-0">Broadcast messages to all users</p>
    </div>
    <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>New Announcement
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-modern mb-0">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Target</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($announcements as $announcement)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($announcement->is_pinned)
                            <i class="bi bi-pin-fill text-primary" title="Pinned"></i>
                            @endif
                            @if($announcement->is_important)
                            <span class="badge bg-danger">Important</span>
                            @endif
                            <strong>{{ $announcement->title }}</strong>
                        </div>
                    </td>
                    <td>{{ $announcement->author->name ?? 'Unknown' }}</td>
                    <td>
                        @if($announcement->target_roles)
                        <span class="badge bg-info">{{ count($announcement->target_roles) }} roles</span>
                        @else
                        <span class="badge bg-secondary">All Users</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $isActive = (!$announcement->published_at || $announcement->published_at <= now()) 
                                && (!$announcement->expires_at || $announcement->expires_at > now());
                        @endphp
                        @if($isActive)
                        <span class="badge bg-success">Active</span>
                        @elseif($announcement->published_at && $announcement->published_at > now())
                        <span class="badge bg-warning text-dark">Scheduled</span>
                        @else
                        <span class="badge bg-secondary">Expired</span>
                        @endif
                    </td>
                    <td>{{ $announcement->published_at?->format('d M Y H:i') ?? '-' }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('admin.announcements.edit', $announcement) }}" 
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.announcements.resend', $announcement) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-info" title="Resend Push">
                                    <i class="bi bi-send"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.announcements.destroy', $announcement) }}" 
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this announcement?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-megaphone display-4 d-block mb-3 opacity-25"></i>
                        No announcements yet
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($announcements->hasPages())
    <div class="card-footer">
        {{ $announcements->links() }}
    </div>
    @endif
</div>
@endsection
