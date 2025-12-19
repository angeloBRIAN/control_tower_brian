@extends('layouts.app')

@section('title', 'Foremen')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-tools me-2"></i>Foremen</h1>
    </div>
    <a href="{{ route('foremen.create') }}" class="btn btn-primary">
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
                    @forelse($foremen as $index => $foreman)
                    <tr>
                        <td>{{ $foremen->firstItem() + $index }}</td>
                        <td>{{ $foreman->name }}</td>
                        <td>
                            @if($foreman->franchise == 'PC')
                                <span class="badge bg-primary">PC</span>
                            @elseif($foreman->franchise == 'CV')
                                <span class="badge bg-info">CV</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($foreman->active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('foremen.edit', $foreman) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('foremen.destroy', $foreman) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this foreman?');">
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
                        <td colspan="5" class="text-center text-muted py-4">No foremen found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($foremen->hasPages())
    <div class="card-footer">
        {{ $foremen->links() }}
    </div>
    @endif
</div>
@endsection
