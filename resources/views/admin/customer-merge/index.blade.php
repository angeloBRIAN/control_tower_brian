@extends('layouts.app')

@section('title', 'Customer Merge Suggestions')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-people me-2"></i>Customer Merge Suggestions</h1>
        <p class="text-muted mb-0">Merge duplicate customer names to clean up data</p>
    </div>
    <form action="{{ route('admin.customer-merge.refresh') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span>Scanning...'; this.form.submit();">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh Suggestions
        </button>
    </form>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-warning">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold text-warning">{{ $stats['pending'] }}</div>
                <div class="text-muted">Pending Review</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold text-success">{{ $stats['merged'] }}</div>
                <div class="text-muted">Merged</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-secondary">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold text-secondary">{{ $stats['ignored'] }}</div>
                <div class="text-muted">Ignored</div>
            </div>
        </div>
    </div>
</div>

@if($suggestions->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-check-circle text-success d-block mb-3" style="font-size: 3rem;"></i>
        <h5>No pending merge suggestions</h5>
        <p class="text-muted mb-3">Click "Refresh Suggestions" to scan for duplicate customer names.</p>
    </div>
</div>
@else
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Customer Name A</th>
                    <th>Customer Name B</th>
                    <th class="text-center">Similarity</th>
                    <th class="text-center">Jobs A</th>
                    <th class="text-center">Jobs B</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($suggestions as $suggestion)
                <tr>
                    <td>
                        <strong>{{ $suggestion->customer_name_a }}</strong>
                    </td>
                    <td>
                        {{ $suggestion->customer_name_b }}
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $suggestion->similarity_score >= 90 ? 'danger' : 'warning' }}">
                            {{ number_format($suggestion->similarity_score, 0) }}%
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary">{{ $suggestion->jobs_count_a }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary">{{ $suggestion->jobs_count_b }}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <form action="{{ route('admin.customer-merge.merge', $suggestion) }}" method="POST" class="d-inline" onsubmit="return confirm('Merge \'{{ $suggestion->customer_name_b }}\' into \'{{ $suggestion->customer_name_a }}\'?')">
                                @csrf
                                <button type="submit" class="btn btn-success" title="Merge B into A">
                                    <i class="bi bi-arrow-left-right"></i> Merge
                                </button>
                            </form>
                            <form action="{{ route('admin.customer-merge.ignore', $suggestion) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary" title="Ignore">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($suggestions->hasPages())
    <div class="card-footer">
        {{ $suggestions->links() }}
    </div>
    @endif
</div>
@endif

<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-info-circle me-1"></i>How Merging Works
    </div>
    <div class="card-body">
        <p class="mb-2">When you click "Merge":</p>
        <ul class="mb-0">
            <li>All jobs with <strong>Customer Name B</strong> will be updated to use <strong>Customer Name A</strong></li>
            <li>This helps clean up duplicate or misspelled customer names</li>
            <li>The suggestion is marked as "Merged" and removed from the list</li>
        </ul>
    </div>
</div>

@if($stats['ignored'] > 0)
<div class="mt-3">
    <form action="{{ route('admin.customer-merge.clear-ignored') }}" method="POST" class="d-inline" onsubmit="return confirm('Clear all ignored suggestions?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-trash me-1"></i>Clear {{ $stats['ignored'] }} Ignored Suggestion(s)
        </button>
    </form>
</div>
@endif
@endsection
