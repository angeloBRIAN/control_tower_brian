@extends('layouts.app')

@section('title', 'Orphan Vehicles Report')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-car-front me-2"></i>Orphan Vehicles Report</h1>
        <p class="text-muted mb-0">Vehicles with no associated jobs (potential typos or outdated records)</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-warning">{{ $totalOrphans }}</h3>
                <small class="text-muted">Orphan Vehicles</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-primary">{{ $totalVehicles }}</h3>
                <small class="text-muted">Total Vehicles</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-success">{{ $totalVehicles - $totalOrphans }}</h3>
                <small class="text-muted">With Jobs</small>
            </div>
        </div>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    <strong>About this report:</strong> These vehicles have no associated jobs. This can happen when:
    <ul class="mb-0 mt-2">
        <li>A plate number typo was corrected during import (e.g., "L808DNNU" → "L808DNU")</li>
        <li>All jobs for a vehicle have been deleted</li>
        <li>Data import errors created invalid vehicle records</li>
    </ul>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Plate, Customer, Model..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
            <div class="col-md-3">
                <a href="{{ route('reports.orphan-vehicles') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Results Table -->
<form id="bulkDeleteForm" action="{{ route('reports.orphan-vehicles.bulk-destroy') }}" method="POST">
    @csrf
    @method('DELETE')
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-table me-2"></i>Orphan Vehicles</span>
            <div>
                <span class="badge bg-warning text-dark me-2">{{ $orphans->total() }} records</span>
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete selected orphan vehicles?')">
                    <i class="bi bi-trash me-1"></i>Delete Selected
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Plate Number</th>
                            <th>Customer</th>
                            <th>Model</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orphans as $vehicle)
                        <tr>
                            <td><input type="checkbox" name="vehicle_ids[]" value="{{ $vehicle->id }}"></td>
                            <td><code class="text-warning">{{ $vehicle->plate_number }}</code></td>
                            <td>{{ Str::limit($vehicle->customer_name, 30) ?? '-' }}</td>
                            <td>{{ $vehicle->model ?? '-' }}</td>
                            <td>{{ $vehicle->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <form action="{{ route('reports.orphan-vehicles.destroy', $vehicle) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this vehicle?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-check-circle display-4 d-block mb-2 text-success"></i>
                                No orphan vehicles found!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orphans->hasPages())
        <div class="card-footer">
            {{ $orphans->links() }}
        </div>
        @endif
    </div>
</form>

@push('scripts')
<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('input[name="vehicle_ids[]"]').forEach(cb => cb.checked = this.checked);
    });
</script>
@endpush
@endsection
