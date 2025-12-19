@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-clock-history me-2"></i>Audit Logs</h1>
        <p class="text-muted">Track all data changes in the system</p>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Model Type</label>
                <select name="model" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach($modelTypes as $type)
                        <option value="{{ $type['value'] }}" {{ request('model') == $type['value'] ? 'selected' : '' }}>
                            {{ $type['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Action</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">All Actions</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm me-2">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Results -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th style="width: 150px;">Date/Time</th>
                    <th style="width: 120px;">User</th>
                    <th style="width: 100px;">Model</th>
                    <th style="width: 60px;">ID</th>
                    <th style="width: 80px;">Action</th>
                    <th>Changes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>
                        <small>{{ $log->created_at->format('d/m/Y') }}</small><br>
                        <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                    </td>
                    <td>{{ $log->user?->name ?? 'System' }}</td>
                    <td>{{ $log->model_name }}</td>
                    <td><code>{{ $log->auditable_id }}</code></td>
                    <td>
                        <span class="badge bg-{{ $log->action_color }}">{{ ucfirst($log->action) }}</span>
                    </td>
                    <td>
                        @if($log->action === 'created')
                            <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#log{{ $log->id }}">
                                <i class="bi bi-eye"></i> View New Values
                            </button>
                            <div class="collapse mt-2" id="log{{ $log->id }}">
                                <div class="card card-body bg-light p-2">
                                    <small><pre class="mb-0" style="font-size: 11px; max-height: 200px; overflow: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre></small>
                                </div>
                            </div>
                        @elseif($log->action === 'updated')
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#log{{ $log->id }}">
                                <i class="bi bi-eye"></i> View Changes ({{ count($log->new_values ?? []) }} fields)
                            </button>
                            <div class="collapse mt-2" id="log{{ $log->id }}">
                                <div class="card card-body bg-light p-2">
                                    <table class="table table-sm table-bordered mb-0" style="font-size: 11px;">
                                        <thead>
                                            <tr>
                                                <th>Field</th>
                                                <th>Old Value</th>
                                                <th>New Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($log->new_values ?? [] as $field => $newValue)
                                            <tr>
                                                <td><strong>{{ $field }}</strong></td>
                                                <td class="text-danger">
                                                    {{ is_array($log->old_values[$field] ?? null) || is_object($log->old_values[$field] ?? null) ? json_encode($log->old_values[$field] ?? null) : ($log->old_values[$field] ?? '-') }}
                                                </td>
                                                <td class="text-success">
                                                    {{ is_array($newValue) || is_object($newValue) ? json_encode($newValue) : $newValue }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @elseif($log->action === 'deleted')
                            <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#log{{ $log->id }}">
                                <i class="bi bi-eye"></i> View Deleted Data
                            </button>
                            <div class="collapse mt-2" id="log{{ $log->id }}">
                                <div class="card card-body bg-light p-2">
                                    <small><pre class="mb-0" style="font-size: 11px; max-height: 200px; overflow: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre></small>
                                </div>
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No audit logs found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $logs->links() }}
</div>
@endsection
