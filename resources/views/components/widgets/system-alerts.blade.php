{{-- Widget: System Alerts --}}
@props(['systemAlerts' => []])

<div class="card h-100">
    <div class="card-header-modern">
        <span class="card-header-title">
            <i class="bi bi-gear-wide-connected text-info"></i>System Status
        </span>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            {{-- Backup Status --}}
            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-database-check text-success"></i>
                    <span>Database Backup</span>
                </div>
                @if(isset($systemAlerts['last_backup']))
                <span class="badge bg-success">{{ \Carbon\Carbon::parse($systemAlerts['last_backup'])->diffForHumans() }}</span>
                @else
                <span class="badge bg-danger">No backup</span>
                @endif
            </li>
            
            {{-- Import Errors --}}
            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-cloud-arrow-up text-{{ ($systemAlerts['import_errors'] ?? 0) > 0 ? 'warning' : 'success' }}"></i>
                    <span>Import Errors</span>
                </div>
                @if(($systemAlerts['import_errors'] ?? 0) > 0)
                <span class="badge bg-warning text-dark">{{ $systemAlerts['import_errors'] }} pending</span>
                @else
                <span class="badge bg-success">All clear</span>
                @endif
            </li>
            
            {{-- Disk Space --}}
            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-hdd text-{{ ($systemAlerts['disk_usage'] ?? 0) > 80 ? 'danger' : 'success' }}"></i>
                    <span>Disk Usage</span>
                </div>
                <span class="badge bg-{{ ($systemAlerts['disk_usage'] ?? 0) > 80 ? 'danger' : (($systemAlerts['disk_usage'] ?? 0) > 60 ? 'warning' : 'success') }}">
                    {{ $systemAlerts['disk_usage'] ?? 0 }}%
                </span>
            </li>
            
            {{-- Scheduler Status --}}
            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-{{ ($systemAlerts['scheduler_healthy'] ?? true) ? 'success' : 'danger' }}"></i>
                    <span>Scheduler</span>
                </div>
                @if($systemAlerts['scheduler_healthy'] ?? true)
                <span class="badge bg-success">Running</span>
                @else
                <span class="badge bg-danger">Stopped</span>
                @endif
            </li>
        </ul>
    </div>
</div>
