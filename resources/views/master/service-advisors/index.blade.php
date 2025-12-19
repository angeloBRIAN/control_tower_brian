@extends('layouts.app')

@section('title', 'Service Advisors')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-person-badge me-2"></i>Service Advisors</h1>
    </div>
    <a href="{{ route('service-advisors.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add New
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Name</th>
                        <th>Franchise</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($serviceAdvisors as $index => $sa)
                    <tr>
                        <td>{{ $serviceAdvisors->firstItem() + $index }}</td>
                        <td>{{ $sa->name }}</td>
                        <td>
                            @if($sa->franchise == 'PC')
                                <span class="badge bg-primary">PC</span>
                            @elseif($sa->franchise == 'CV')
                                <span class="badge bg-info">CV</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($sa->active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('service-advisors.edit', $sa) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('service-advisors.destroy', $sa) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this advisor?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No service advisors found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($serviceAdvisors->hasPages())
    <div class="card-footer">
        {{ $serviceAdvisors->links() }}
    </div>
    @endif
</div>
@endsection
