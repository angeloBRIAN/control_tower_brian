{{-- Widget: Towing Schedule --}}
@props(['towingSchedule' => collect()])

<div class="card h-100">
    <div class="card-header-modern">
        <span class="card-header-title">
            <i class="bi bi-truck text-warning"></i>Towing Schedule
        </span>
        <a href="{{ route('towing-records.index') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">View All</a>
    </div>
    <div class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
        @forelse($towingSchedule as $towing)
        <a href="{{ route('towing-records.show', $towing) }}" class="list-group-item list-group-item-action py-3">
            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                <strong>{{ $towing->plate_number ?? 'N/A' }}</strong>
                <span class="badge bg-{{ $towing->status == 'pending' ? 'warning text-dark' : ($towing->status == 'in_transit' ? 'info' : 'success') }}">
                    {{ ucfirst(str_replace('_', ' ', $towing->status ?? 'pending')) }}
                </span>
            </div>
            <p class="mb-1 small text-muted">
                <i class="bi bi-geo-alt me-1"></i>{{ Str::limit($towing->pickup_location ?? 'Unknown', 30) }}
            </p>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ $towing->customer_name ?? 'Unknown' }}</small>
                <small class="text-primary">
                    <i class="bi bi-clock me-1"></i>
                    {{ $towing->scheduled_time?->format('H:i') ?? 'TBD' }}
                </small>
            </div>
        </a>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-truck display-4 d-block mb-3 opacity-25"></i>
            No towing pickups scheduled
        </div>
        @endforelse
    </div>
</div>
