{{-- Widget: My Performance (SA/Foreman KPIs) --}}
@props(['performance' => []])

<div class="card h-100">
    <div class="card-header-modern">
        <span class="card-header-title">
            <i class="bi bi-graph-up-arrow text-success"></i>My Performance
        </span>
        <span class="badge bg-light text-dark">This Month</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6">
                <div class="text-center p-3 rounded bg-success-subtle">
                    <h3 class="mb-0 text-success">{{ $performance['jobs_closed'] ?? 0 }}</h3>
                    <small class="text-muted">Jobs Closed</small>
                </div>
            </div>
            <div class="col-6">
                <div class="text-center p-3 rounded bg-primary-subtle">
                    <h3 class="mb-0 text-primary">{{ $performance['jobs_in_progress'] ?? 0 }}</h3>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
            <div class="col-6">
                <div class="text-center p-3 rounded bg-warning-subtle">
                    <h3 class="mb-0 text-warning">Rp {{ number_format(($performance['revenue'] ?? 0) / 1000000, 1) }}M</h3>
                    <small class="text-muted">Revenue</small>
                </div>
            </div>
            <div class="col-6">
                <div class="text-center p-3 rounded bg-info-subtle">
                    <h3 class="mb-0 text-info">{{ number_format($performance['avg_days'] ?? 0, 1) }}</h3>
                    <small class="text-muted">Avg Days/Job</small>
                </div>
            </div>
        </div>
        
        @if(isset($performance['trend']))
        <div class="mt-3 pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center">
                <span class="small text-muted">vs Last Month</span>
                @if(($performance['trend'] ?? 0) >= 0)
                <span class="badge bg-success"><i class="bi bi-arrow-up me-1"></i>{{ $performance['trend'] }}%</span>
                @else
                <span class="badge bg-danger"><i class="bi bi-arrow-down me-1"></i>{{ abs($performance['trend']) }}%</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
