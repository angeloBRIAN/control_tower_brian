@extends('layouts.app')

@section('title', 'WIP Conflict Report')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-exclamation-diamond me-2"></i>WIP Conflict Report</h1>
        <p class="text-muted mb-0">Jobs with temporary or incorrect WIP numbers</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-warning">{{ $stats['total'] }}</h3>
                <small class="text-muted">Total Conflicts</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-info">{{ $stats['dup'] }}</h3>
                <small class="text-muted">Duplicate WIPs (-DUP-)</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-3">
                <h3 class="mb-0 text-danger">{{ $stats['wrong'] }}</h3>
                <small class="text-muted">Wrong Holder (-WRONG-)</small>
            </div>
        </div>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    <strong>About this report:</strong>
    <ul class="mb-0 mt-2">
        <li><strong>-DUP-</strong> jobs were created when a WIP was already assigned to a different vehicle during Progress import.</li>
        <li><strong>-WRONG-</strong> jobs are demoted holders whose WIP was reassigned to the correct vehicle during Invoice/Uninvoiced import.</li>
        <li>These will be automatically resolved when you import the correct Invoice/Uninvoiced data, or you can manually fix them below.</li>
    </ul>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="WIP, Plate, Customer..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Franchise</label>
                <select name="franchise" class="form-select">
                    <option value="">All</option>
                    <option value="PC" {{ request('franchise') === 'PC' ? 'selected' : '' }}>PC</option>
                    <option value="CV" {{ request('franchise') === 'CV' ? 'selected' : '' }}>CV</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All</option>
                    <option value="dup" {{ request('type') === 'dup' ? 'selected' : '' }}>Duplicate (-DUP-)</option>
                    <option value="wrong" {{ request('type') === 'wrong' ? 'selected' : '' }}>Wrong (-WRONG-)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('reports.wip-conflicts') }}" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Results Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-2"></i>Conflict Jobs</span>
        <span class="badge bg-warning text-dark">{{ $conflicts->total() }} records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Current WIP</th>
                        <th>Original WIP</th>
                        <th>Plate Number</th>
                        <th>Customer</th>
                        <th>Franchise</th>
                        <th>Job Date</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conflicts as $job)
                    @php
                        // Extract original WIP
                        $originalWip = preg_replace('/-(DUP|WRONG)-\d+$/', '', $job->job_number);
                        $type = str_contains($job->job_number, '-WRONG-') ? 'wrong' : 'dup';
                    @endphp
                    <tr>
                        <td>
                            <code class="{{ $type === 'wrong' ? 'text-danger' : 'text-warning' }}">{{ $job->job_number }}</code>
                        </td>
                        <td><code>{{ $originalWip }}</code></td>
                        <td><strong>{{ $job->plate_number ?? '-' }}</strong></td>
                        <td>{{ Str::limit($job->customer_name, 25) ?? '-' }}</td>
                        <td><span class="badge {{ $job->franchise === 'PC' ? 'bg-primary' : 'bg-secondary' }}">{{ $job->franchise }}</span></td>
                        <td>{{ $job->job_date ? $job->job_date->format('d/m/Y') : '-' }}</td>
                        <td>
                            @if($type === 'wrong')
                                <span class="badge bg-danger">Wrong Holder</span>
                            @else
                                <span class="badge bg-warning text-dark">Duplicate</span>
                            @endif
                        </td>
                        <td class="d-flex gap-1">
                            <a href="{{ route('jobs.show', $job) }}" class="btn btn-sm btn-outline-primary" target="_blank" title="View Job">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#resolveModal{{ $job->id }}" title="Manual Fix">
                                <i class="bi bi-check-circle"></i>
                            </button>
                        </td>
                    </tr>
                    
                    <!-- Resolve Modal -->
                    <div class="modal fade" id="resolveModal{{ $job->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('reports.wip-conflicts.resolve', $job) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Resolve WIP Conflict</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Current WIP:</strong> <code>{{ $job->job_number }}</code></p>
                                        <p><strong>Plate:</strong> {{ $job->plate_number }}</p>
                                        <p><strong>Customer:</strong> {{ $job->customer_name ?? '-' }}</p>
                                        <hr>
                                        <div class="mb-3">
                                            <label class="form-label">Enter Correct WIP Number</label>
                                            <input type="text" name="new_wip" class="form-control" value="{{ $originalWip }}" required>
                                            <div class="form-text">This will update the job's WIP and mark it as resolved.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle me-1"></i>Resolve
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-check-circle display-4 d-block mb-2 text-success"></i>
                            No WIP conflicts found!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($conflicts->hasPages())
    <div class="card-footer">
        {{ $conflicts->links() }}
    </div>
    @endif
</div>
@endsection
