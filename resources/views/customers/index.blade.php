@extends('layouts.app')

@section('title', 'Customer Lookup')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-people me-2"></i>Customer Lookup</h1>
        <p class="text-muted">{{ number_format($totalCustomers) }} unique customers found</p>
    </div>
    <a href="{{ route('customers.duplicates') }}" class="btn btn-warning">
        <i class="bi bi-arrow-left-right me-1"></i>Merge Duplicates
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center" id="searchForm">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search customer name..." value="{{ $search }}" autofocus>
                </div>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-sm mb-0">
                <thead class="table-dark">
                    @php
                        $currentSort = $sortField ?? 'name';
                        $currentDir = $sortDir ?? 'asc';
                    @endphp
                    <tr>
                        <th>#</th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => ($currentSort === 'name' && $currentDir === 'asc') ? 'desc' : 'asc']) }}" class="text-white text-decoration-none d-flex align-items-center justify-content-between">
                                Customer Name
                                @if($currentSort === 'name')
                                    <i class="bi bi-arrow-{{ $currentDir === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="bi bi-arrow-down-up ms-1 opacity-25"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'vehicle_count', 'dir' => ($currentSort === 'vehicle_count' && $currentDir === 'asc') ? 'desc' : 'asc']) }}" class="text-white text-decoration-none d-flex align-items-center justify-content-center">
                                Vehicles
                                @if($currentSort === 'vehicle_count')
                                    <i class="bi bi-arrow-{{ $currentDir === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="bi bi-arrow-down-up ms-1 opacity-25"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'job_count', 'dir' => ($currentSort === 'job_count' && $currentDir === 'asc') ? 'desc' : 'asc']) }}" class="text-white text-decoration-none d-flex align-items-center justify-content-center">
                                Total Jobs
                                @if($currentSort === 'job_count')
                                    <i class="bi bi-arrow-{{ $currentDir === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="bi bi-arrow-down-up ms-1 opacity-25"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-center">Uninvoiced</th>
                        <th class="text-end">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'sales_amount', 'dir' => ($currentSort === 'sales_amount' && $currentDir === 'asc') ? 'desc' : 'asc']) }}" class="text-white text-decoration-none d-flex align-items-center justify-content-end">
                                Sales Amount
                                @if($currentSort === 'sales_amount')
                                    <i class="bi bi-arrow-{{ $currentDir === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="bi bi-arrow-down-up ms-1 opacity-25"></i>
                                @endif
                            </a>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customerData as $index => $customer)
                    <tr>
                        <td>{{ $customers->firstItem() + $index }}</td>
                        <td>
                            <a href="{{ route('customers.show', ['name' => $customer->name]) }}" class="fw-bold text-primary">
                                {{ $customer->name }}
                            </a>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ $customer->vehicle_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $customer->job_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($customer->uninvoiced_count > 0)
                                <span class="badge bg-warning text-dark">{{ $customer->uninvoiced_count }}</span>
                            @else
                                <span class="badge bg-success">0</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($customer->sales_amount > 0)
                                <span class="text-success fw-bold">{{ number_format($customer->sales_amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('customers.show', ['name' => $customer->name]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            @if($search)
                                No customers found matching "{{ $search }}"
                            @else
                                No customers found
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="d-flex align-items-center">
        <label class="me-2 small text-muted">Show</label>
        <select name="per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()" form="searchForm">
            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
        </select>
        <span class="ms-2 small text-muted">entries</span>
    </div>
    {{ $customers->links() }}
</div>
@endsection
