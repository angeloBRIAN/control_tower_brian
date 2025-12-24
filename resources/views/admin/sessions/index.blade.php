@extends('layouts.app')

@section('title', 'Session Manager')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-display me-2"></i>Session Manager</h1>
        <p class="text-muted">View and manage all active user sessions</p>
    </div>
    <div>
        <form action="{{ route('admin.sessions.cleanup') }}" method="POST" class="d-inline" onsubmit="return confirm('Clean up sessions inactive for {{ $schedule->session_cleanup_days ?? 7 }} days?');">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">
                <i class="bi bi-trash me-1"></i>Cleanup Inactive
            </button>
        </form>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Online Now</h6>
                        <h2 class="mb-0">{{ $stats['online_now'] }}</h2>
                    </div>
                    <i class="bi bi-circle-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Logins Today</h6>
                        <h2 class="mb-0">{{ $stats['today_logins'] }}</h2>
                    </div>
                    <i class="bi bi-calendar-day fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Active Users Today</h6>
                        <h2 class="mb-0">{{ $stats['unique_users_today'] }}</h2>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Sessions</h6>
                        <h2 class="mb-0">{{ $stats['total_sessions'] }}</h2>
                    </div>
                    <i class="bi bi-display fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Device Breakdown -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span><strong>Devices:</strong></span>
                    <div>
                        <span class="badge bg-primary me-2"><i class="bi bi-pc-display me-1"></i>Desktop: {{ $stats['devices']['desktop'] }}</span>
                        <span class="badge bg-success me-2"><i class="bi bi-phone me-1"></i>Mobile: {{ $stats['devices']['mobile'] }}</span>
                        <span class="badge bg-warning text-dark"><i class="bi bi-tablet me-1"></i>Tablet: {{ $stats['devices']['tablet'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cleanup Settings Card -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Session Cleanup Settings</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.sessions.settings') }}" method="POST" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-2">
                <label class="form-label">Auto Cleanup</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="session_cleanup_enabled" value="1" id="cleanupEnabled" {{ ($schedule->session_cleanup_enabled ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="cleanupEnabled">Enable</label>
                </div>
            </div>
            <div class="col-md-3">
                <label for="session_cleanup_days" class="form-label">Remove inactive after (days)</label>
                <input type="number" class="form-control" name="session_cleanup_days" id="session_cleanup_days" min="1" max="365" value="{{ $schedule->session_cleanup_days ?? 7 }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save</button>
            </div>
            <div class="col-md-5 text-muted small">
                <i class="bi bi-info-circle me-1"></i>Sessions inactive for longer than this will be automatically removed. Runs daily via scheduler.
            </div>
        </form>
    </div>
</div>

<!-- Filter by User -->
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Filter by User</label>
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Device Type</label>
                <select name="device" class="form-select">
                    <option value="">All Devices</option>
                    <option value="desktop" {{ request('device') == 'desktop' ? 'selected' : '' }}>Desktop</option>
                    <option value="mobile" {{ request('device') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                    <option value="tablet" {{ request('device') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-filter me-1"></i>Filter</button>
                <a href="{{ route('admin.sessions.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Sessions Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>User</th>
                        <th>Device</th>
                        <th>Browser / Platform</th>
                        <th>IP Address</th>
                        <th>Last Active</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php $currentSessionId = session()->getId(); @endphp
                    @forelse($sessions as $session)
                    @php $isCurrentSession = $session->session_id === $currentSessionId; @endphp
                    <tr class="{{ $isCurrentSession ? 'table-primary' : '' }}">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                    {{ strtoupper(substr($session->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $session->user->name ?? 'Unknown' }}</strong>
                                    @if($isCurrentSession)
                                    <span class="badge bg-primary ms-1">This Device</span>
                                    @endif
                                    <br><small class="text-muted">{{ $session->user->email ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <i class="bi bi-{{ $session->device_icon }} fs-4 text-{{ $session->device_type === 'mobile' ? 'success' : 'primary' }}"></i>
                            <span class="ms-1">{{ ucfirst($session->device_type ?? 'Unknown') }}</span>
                        </td>
                        <td>
                            {{ $session->browser ?? 'Unknown' }} / {{ $session->platform ?? 'Unknown' }}
                        </td>
                        <td><code>{{ $session->ip_address ?? '-' }}</code></td>
                        <td>{{ $session->last_active_at?->diffForHumans() ?? 'Unknown' }}</td>
                        <td>
                            @if($isCurrentSession)
                            <span class="badge bg-primary"><i class="bi bi-circle-fill me-1"></i>Current</span>
                            @elseif($session->last_active_at && $session->last_active_at >= now()->subMinutes(5))
                            <span class="badge bg-success"><i class="bi bi-circle-fill me-1"></i>Online</span>
                            @elseif($session->last_active_at && $session->last_active_at >= now()->subHours(1))
                            <span class="badge bg-warning text-dark">Idle</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            @if($isCurrentSession)
                            <span class="text-muted" title="Cannot terminate current session"><i class="bi bi-lock"></i></span>
                            @else
                            <form action="{{ route('admin.sessions.terminate', $session) }}" method="POST" 
                                  onsubmit="return confirm('Terminate this session for {{ $session->user->name ?? 'user' }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Terminate Session">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-display display-4 d-block mb-3 opacity-25"></i>
                            No active sessions found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $sessions->links() }}
</div>
@endsection
