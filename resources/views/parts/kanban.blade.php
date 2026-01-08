@extends('layouts.app')

@section('title', 'Parts Tracking - Kanban')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-kanban me-2"></i>Parts Tracking
            </h1>
            <p class="text-muted mb-0">Drag and drop to update status</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('part-orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-list me-1"></i>List View
            </a>
            <a href="{{ route('part-orders.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Add Part Order
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-25 p-3 me-3">
                        <i class="bi bi-box-seam fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="h3 mb-0">{{ $summary['pending'] }}</div>
                        <div class="text-muted small">Pending Orders</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-25 p-3 me-3">
                        <i class="bi bi-clock-history fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="h3 mb-0">{{ $summary['due_soon'] }}</div>
                        <div class="text-muted small">Due Within 7 Days</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-25 p-3 me-3">
                        <i class="bi bi-exclamation-triangle fs-4 text-danger"></i>
                    </div>
                    <div>
                        <div class="h3 mb-0">{{ $summary['overdue'] }}</div>
                        <div class="text-muted small">Overdue</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="kanban-board">
        <div class="row flex-nowrap overflow-auto pb-3" style="min-height: 500px;">
            @foreach($statuses as $statusKey => $statusInfo)
                @if($statusKey !== 'cancelled')
                <div class="col-kanban" style="min-width: 280px; max-width: 320px;">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
                            <div class="d-flex align-items-center">
                                <span class="badge rounded-pill me-2" style="background-color: {{ $statusInfo['color'] }}">
                                    {{ count($ordersByStatus[$statusKey] ?? []) }}
                                </span>
                                <span class="fw-semibold">{{ $statusInfo['label'] }}</span>
                            </div>
                            <i class="bi {{ $statusInfo['icon'] }} text-muted"></i>
                        </div>
                        <div class="card-body kanban-column p-2" 
                             data-status="{{ $statusKey }}"
                             style="min-height: 400px; background: var(--bs-light); border-radius: 0.5rem;">
                            @forelse($ordersByStatus[$statusKey] ?? [] as $order)
                                <div class="kanban-card card border-0 shadow-sm mb-2 cursor-grab" 
                                     data-order-id="{{ $order->id }}"
                                     draggable="true">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0 fw-semibold">{{ $order->part_name }}</h6>
                                            @if($order->is_overdue)
                                                <span class="badge bg-danger">Overdue</span>
                                            @elseif($order->is_due_soon)
                                                <span class="badge bg-warning text-dark">Due Soon</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted mb-2">
                                            <i class="bi bi-file-text me-1"></i>
                                            <a href="{{ route('jobs.show', $order->job_id) }}" class="text-decoration-none">
                                                {{ $order->job->job_number ?? 'N/A' }}
                                            </a>
                                        </div>
                                        @if($order->part_number)
                                            <div class="small text-muted mb-2">
                                                <i class="bi bi-upc me-1"></i>{{ $order->part_number }}
                                            </div>
                                        @endif
                                        @if($order->rq)
                                            <div class="small text-muted mb-2">
                                                <i class="bi bi-receipt me-1"></i>RQ: {{ $order->rq }}
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                {{ $order->expected_date?->format('d M Y') }}
                                            </small>
                                            <small class="text-muted">
                                                Qty: {{ $order->quantity }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 opacity-25"></i>
                                    <p class="small mt-2 mb-0">No orders</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

@push('styles')
<style>
.kanban-board {
    overflow-x: auto;
}
.kanban-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.kanban-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}
.kanban-card.dragging {
    opacity: 0.5;
    transform: rotate(3deg);
}
.kanban-column.drag-over {
    background: rgba(var(--bs-primary-rgb), 0.1) !important;
    border: 2px dashed var(--bs-primary);
}
.cursor-grab {
    cursor: grab;
}
.cursor-grab:active {
    cursor: grabbing;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-column');

    cards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });

    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('dragenter', handleDragEnter);
        column.addEventListener('dragleave', handleDragLeave);
        column.addEventListener('drop', handleDrop);
    });

    let draggedCard = null;

    function handleDragStart(e) {
        draggedCard = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', this.dataset.orderId);
    }

    function handleDragEnd(e) {
        this.classList.remove('dragging');
        columns.forEach(col => col.classList.remove('drag-over'));
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    function handleDragEnter(e) {
        this.classList.add('drag-over');
    }

    function handleDragLeave(e) {
        this.classList.remove('drag-over');
    }

    function handleDrop(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        
        const orderId = e.dataTransfer.getData('text/plain');
        const newStatus = this.dataset.status;
        
        if (draggedCard) {
            // Optimistic UI update
            this.appendChild(draggedCard);
            
            // Send AJAX request
            fetch(`/part-orders/${orderId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert on error
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                location.reload();
            });
        }
    }
});
</script>
@endpush
@endsection
