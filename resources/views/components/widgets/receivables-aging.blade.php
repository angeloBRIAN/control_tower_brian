{{-- Widget: Receivables Aging --}}
@props(['receivablesAging' => []])

@php
    $segments = [
        ['label' => '0-30 days', 'key' => '0_30', 'color' => 'success'],
        ['label' => '31-60 days', 'key' => '31_60', 'color' => 'info'],
        ['label' => '61-90 days', 'key' => '61_90', 'color' => 'warning'],
        ['label' => '>90 days', 'key' => '90_plus', 'color' => 'danger'],
    ];
    $total = array_sum($receivablesAging);
@endphp

<div class="card h-100">
    <div class="card-header-modern">
        <span class="card-header-title">
            <i class="bi bi-receipt-cutoff text-danger"></i>Receivables Aging
        </span>
        <a href="{{ route('reports.builder', ['type' => 'receivables']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Report</a>
    </div>
    <div class="card-body">
        <div class="text-center mb-3">
            <h3 class="mb-0">Rp {{ number_format($total / 1000000, 1) }}M</h3>
            <small class="text-muted">Total Outstanding</small>
        </div>
        
        @foreach($segments as $segment)
        @php $amount = $receivablesAging[$segment['key']] ?? 0; @endphp
        <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-muted">{{ $segment['label'] }}</small>
                <small class="fw-bold text-{{ $segment['color'] }}">Rp {{ number_format($amount / 1000000, 1) }}M</small>
            </div>
            <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-{{ $segment['color'] }}" 
                     style="width: {{ $total > 0 ? ($amount / $total) * 100 : 0 }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>
