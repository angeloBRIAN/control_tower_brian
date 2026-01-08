@extends('layouts.app')

@section('title', 'DMS Customers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">DMS Customers</li>
            </ol>
        </nav>
        <h1><i class="bi bi-people me-2"></i>DMS Customers</h1>
        <p class="text-muted mb-0">Customers imported from DMS system</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">Total Customers</div>
                        <div class="h4 mb-0">{{ number_format($stats['total']) }}</div>
                    </div>
                    <i class="bi bi-people display-6 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">DMS Imported</div>
                        <div class="h4 mb-0">{{ number_format($stats['dms_imported']) }}</div>
                    </div>
                    <i class="bi bi-cloud-download display-6 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">With Vehicles</div>
                        <div class="h4 mb-0">{{ number_format($stats['with_vehicles']) }}</div>
                    </div>
                    <i class="bi bi-truck display-6 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small">With Jobs</div>
                        <div class="h4 mb-0">{{ number_format($stats['with_jobs']) }}</div>
                    </div>
                    <i class="bi bi-wrench display-6 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body py-2">
        <form action="{{ route('dms-customers.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search name, email, phone, DMS magic..." value="{{ $search }}">
            </div>
            <div class="col-md-3">
                <select name="filter" class="form-select">
                    <option value="">All Customers</option>
                    <option value="dms_only" {{ $filter === 'dms_only' ? 'selected' : '' }}>DMS Imported Only</option>
                    <option value="with_vehicles" {{ $filter === 'with_vehicles' ? 'selected' : '' }}>With Vehicles</option>
                    <option value="with_jobs" {{ $filter === 'with_jobs' ? 'selected' : '' }}>With Jobs</option>
                    <option value="no_vehicles" {{ $filter === 'no_vehicles' ? 'selected' : '' }}>No Vehicles</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Search
                </button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('dms-customers.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Customer List -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>
                        <a href="{{ route('dms-customers.index', array_merge(request()->query(), ['sort' => 'dms_magic', 'dir' => $sortField === 'dms_magic' && $sortDir === 'asc' ? 'desc' : 'asc'])) }}" class="text-white text-decoration-none">
                            DMS # {!! $sortField === 'dms_magic' ? ($sortDir === 'asc' ? '↑' : '↓') : '' !!}
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('dms-customers.index', array_merge(request()->query(), ['sort' => 'name', 'dir' => $sortField === 'name' && $sortDir === 'asc' ? 'desc' : 'asc'])) }}" class="text-white text-decoration-none">
                            Name {!! $sortField === 'name' ? ($sortDir === 'asc' ? '↑' : '↓') : '' !!}
                        </a>
                    </th>
                    <th>Company</th>
                    <th>Contact</th>
                    <th class="text-center">Vehicles</th>
                    <th class="text-center">Jobs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td>
                        @if($customer->dms_magic)
                        <span class="badge bg-info">{{ $customer->dms_magic }}</span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $customer->name ?: '-' }}</strong>
                        @if($customer->department)
                        <span class="badge bg-secondary ms-1">{{ $customer->department }}</span>
                        @endif
                    </td>
                    <td>{{ Str::limit($customer->company_name, 30) ?: '-' }}</td>
                    <td>
                        @if($customer->email)
                        <div class="small"><i class="bi bi-envelope me-1"></i>{{ $customer->email }}</div>
                        @endif
                        @if($customer->phone)
                        <div class="small"><i class="bi bi-phone me-1"></i>{{ $customer->phone }}</div>
                        @endif
                        @if(!$customer->email && !$customer->phone)
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($customer->linked_vehicles_count > 0)
                        <span class="badge bg-success">{{ $customer->linked_vehicles_count }}</span>
                        @else
                        <span class="badge bg-secondary">0</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($customer->linked_jobs_count > 0)
                        <span class="badge bg-primary">{{ $customer->linked_jobs_count }}</span>
                        @else
                        <span class="badge bg-secondary">0</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('dms-customers.show', $customer) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox display-6"></i>
                        <p class="mt-2 mb-0">No customers found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $customers->links() }}
</div>
@endsection
