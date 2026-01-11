{{-- Widget: Upcoming PDI --}}
@props(['upcomingPdi' => collect()])

<div class="card h-100">
    <div class="card-header-modern">
        <span class="card-header-title">
            <i class="bi bi-clipboard-check-fill text-success"></i>Upcoming PDI
        </span>
        <a href="{{ route('pdi-records.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">View All</a>
    </div>
    <div class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
        @forelse($upcomingPdi as $pdi)
        <a href="{{ route('pdi-records.show', $pdi) }}" class="list-group-item list-group-item-action py-3">
            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                <strong>{{ $pdi->vehicle_plate ?? 'N/A' }}</strong>
                <span class="badge bg-{{ $pdi->status == 'scheduled' ? 'info' : ($pdi->status == 'in_progress' ? 'warning' : 'success') }}">
                    {{ ucfirst(str_replace('_', ' ', $pdi->status ?? 'pending')) }}
                </span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ $pdi->customer_name ?? 'Unknown Customer' }}</small>
                <small class="text-primary">
                    <i class="bi bi-calendar me-1"></i>
                    {{ $pdi->scheduled_date?->format('d M H:i') ?? 'TBD' }}
                </small>
            </div>
        </a>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-clipboard-check display-4 d-block mb-3 opacity-25"></i>
            No upcoming PDI inspections
        </div>
        @endforelse
    </div>
</div>
