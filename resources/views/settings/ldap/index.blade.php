@extends('layouts.app')

@section('title', 'LDAP Settings')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1><i class="bi bi-hdd-network me-2"></i>LDAP Servers</h1>
    <a href="{{ route('admin.ldap.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add Server
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Host</th>
                        <th>Port</th>
                        <th>Base DN</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($servers as $server)
                    <tr>
                        <td class="fw-bold">{{ $server->name }}</td>
                        <td>{{ $server->host }}</td>
                        <td>{{ $server->port }}</td>
                        <td><small class="text-muted">{{ $server->base_dn }}</small></td>
                        <td>
                            @if($server->active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.ldap.test', $server) }}" class="btn btn-outline-info" title="Test Connection">
                                    <i class="bi bi-plug"></i>
                                </a>
                                <a href="{{ route('admin.ldap.edit', $server) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.ldap.destroy', $server) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete server?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No LDAP servers configured</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
