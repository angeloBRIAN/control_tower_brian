@extends('layouts.app')

@section('title', 'Edit User Role')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
            <li class="breadcrumb-item active">Edit Role</li>
        </ol>
    </nav>
    <h1><i class="bi bi-person-gear me-2"></i>Edit Role for {{ $user->name }}</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield me-2"></i>Assign Role
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">User</label>
                        <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Role</label>
                        <input type="text" class="form-control" value="{{ $user->getRoleDisplayName() }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                            @foreach($roles as $key => $label)
                            <option value="{{ $key }}" {{ $user->role == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Update Role
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Role Permissions
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <th class="text-center">Access</th>
                        </tr>
                    </thead>
                    <tbody id="permissionTable">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const permissions = {
    'admin': ['User Management', 'Master Data', 'Jobs CRUD', 'Import', 'Reports', 'Remarks', 'Audit'],
    'manager': ['Master Data', 'Jobs CRUD', 'Import', 'Reports', 'Remarks', 'Audit'],
    'control_tower': ['Master Data', 'Jobs CRUD', 'Import', 'Reports', 'Remarks'],
    'sparepart': ['View Jobs', 'Need Parts', 'Reports', 'Remarks'],
    'sa': ['View Jobs', 'Reports', 'Remarks'],
    'foreman': ['View Jobs', 'Reports', 'Remarks'],
    'audit': ['View All', 'Reports', 'Audit Logs'],
    'billing': ['View All Jobs', 'Kanban View', 'Reports', 'Remarks'],
    'user': ['None'],
};

function updatePermissions(role) {
    const table = document.getElementById('permissionTable');
    const perms = permissions[role] || ['None'];
    table.innerHTML = perms.map(p => 
        `<tr><td>${p}</td><td class="text-center"><i class="bi bi-check-circle text-success"></i></td></tr>`
    ).join('');
}

document.querySelector('select[name="role"]').addEventListener('change', e => updatePermissions(e.target.value));
updatePermissions('{{ $user->role }}');
</script>
@endsection
