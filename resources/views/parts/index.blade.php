@extends('layouts.app')

@section('title', 'Part Orders')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-box-seam me-2"></i>Part Orders
            </h1>
            <p class="text-muted mb-0">List of all part orders</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('parts.kanban') }}" class="btn btn-outline-secondary">
                <i class="bi bi-kanban me-1"></i>Kanban View
            </a>
            <a href="{{ route('part-orders.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Add Part Order
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search part name, number, job..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $key => $info)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                {{ $info['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="filter" class="form-select">
                        <option value="">All Orders</option>
                        <option value="due_soon" {{ request('filter') === 'due_soon' ? 'selected' : '' }}>Due Soon (7 days)</option>
                        <option value="overdue" {{ request('filter') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                </div>
                @if(request()->hasAny(['search', 'status', 'filter']))
                <div class="col-md-2">
                    <a href="{{ route('part-orders.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>Clear
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Part</th>
                        <th>Job</th>
                        <th>RQ / Order No.</th>
                        <th>Qty</th>
                        <th>Expected Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($partOrders as $order)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $order->part_name }}</div>
                            @if($order->part_number)
                                <small class="text-muted">{{ $order->part_number }}</small>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('jobs.show', $order->job_id) }}" class="text-decoration-none">
                                {{ $order->job->job_number ?? 'N/A' }}
                            </a>
                            <br>
                            <small class="text-muted">{{ $order->job->plate_number ?? '' }}</small>
                        </td>
                        <td>
                            @if($order->rq)
                                <div><small class="text-muted">RQ:</small> {{ $order->rq }}</div>
                            @endif
                            @if($order->no_order_part)
                                <div><small class="text-muted">Order:</small> {{ $order->no_order_part }}</div>
                            @endif
                            @if(!$order->rq && !$order->no_order_part)
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $order->quantity }}</td>
                        <td>
                            <div>{{ $order->expected_date?->format('d M Y') }}</div>
                            @if($order->is_overdue)
                                <span class="badge bg-danger">{{ abs($order->days_until_expected) }} days overdue</span>
                            @elseif($order->is_due_soon)
                                <span class="badge bg-warning text-dark">{{ $order->days_until_expected }} days left</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background-color: {{ $order->status_color }}">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('part-orders.edit', $order) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('part-orders.destroy', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this part order?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                No part orders found
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($partOrders->hasPages())
        <div class="card-footer bg-transparent">
            {{ $partOrders->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
