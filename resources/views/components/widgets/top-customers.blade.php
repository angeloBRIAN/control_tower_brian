{{-- Widget: Top Customers --}}
@props(['topCustomers' => collect()])

<div class="card h-100">
    <div class="card-header-modern">
        <span class="card-header-title">
            <i class="bi bi-star-fill text-warning"></i>Top Customers
        </span>
        <span class="badge bg-light text-dark">This Month</span>
    </div>
    <div class="list-group list-group-flush">
        @forelse($topCustomers->take(5) as $index => $customer)
        <div class="list-group-item py-3">
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    <div class="avatar-circle {{ $index < 3 ? 'bg-warning' : 'bg-secondary' }} text-white" 
                         style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                        {{ $index + 1 }}
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold text-truncate" style="max-width: 150px;">{{ $customer->customer_name ?? 'Unknown' }}</div>
                    <small class="text-muted">{{ $customer->job_count ?? 0 }} jobs</small>
                </div>
                <div class="text-end">
                    <span class="fw-bold text-success">Rp {{ number_format(($customer->total_revenue ?? 0) / 1000000, 1) }}M</span>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-person display-4 d-block mb-3 opacity-25"></i>
            No customer data
        </div>
        @endforelse
    </div>
</div>
