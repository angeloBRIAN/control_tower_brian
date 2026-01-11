{{-- Widget: Daily Revenue --}}
@props(['dailyRevenue' => []])

@php
    $today = $dailyRevenue['today'] ?? 0;
    $target = $dailyRevenue['target'] ?? 50000000; // 50M default
    $yesterday = $dailyRevenue['yesterday'] ?? 0;
    $percentOfTarget = $target > 0 ? min(100, ($today / $target) * 100) : 0;
    $vsYesterday = $yesterday > 0 ? (($today - $yesterday) / $yesterday) * 100 : 0;
@endphp

<div class="card h-100">
    <div class="card-header-modern">
        <span class="card-header-title">
            <i class="bi bi-cash-stack text-success"></i>Daily Revenue
        </span>
        <span class="badge bg-light text-dark">{{ now()->format('d M Y') }}</span>
    </div>
    <div class="card-body">
        <div class="text-center mb-3">
            <h2 class="mb-0 text-success">Rp {{ number_format($today / 1000000, 1) }}M</h2>
            <small class="text-muted">Today's Invoiced Amount</small>
        </div>
        
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-muted">Daily Target</small>
                <small class="fw-bold">{{ number_format($percentOfTarget, 0) }}%</small>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar {{ $percentOfTarget >= 100 ? 'bg-success' : ($percentOfTarget >= 75 ? 'bg-warning' : 'bg-danger') }}" 
                     style="width: {{ $percentOfTarget }}%"></div>
            </div>
            <small class="text-muted">Target: Rp {{ number_format($target / 1000000, 0) }}M</small>
        </div>
        
        <div class="border-top pt-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="small text-muted">vs Yesterday</span>
                @if($vsYesterday >= 0)
                <span class="badge bg-success"><i class="bi bi-arrow-up me-1"></i>{{ number_format($vsYesterday, 1) }}%</span>
                @else
                <span class="badge bg-danger"><i class="bi bi-arrow-down me-1"></i>{{ number_format(abs($vsYesterday), 1) }}%</span>
                @endif
            </div>
        </div>
    </div>
</div>
