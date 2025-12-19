@extends('layouts.app')

@section('title', isset($server) ? 'Edit LDAP Server' : 'Add LDAP Server')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('admin.ldap.index') }}">LDAP Servers</a></li>
            <li class="breadcrumb-item active">{{ isset($server) ? 'Edit' : 'Add' }}</li>
        </ol>
    </nav>
    <h1>{{ isset($server) ? 'Edit LDAP Server' : 'Add LDAP Server' }}</h1>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-body">
        <form action="{{ isset($server) ? route('admin.ldap.update', $server) : route('admin.ldap.store') }}" method="POST">
            @csrf
            @if(isset($server))
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="form-label">Server Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $server->name ?? '') }}" placeholder="e.g. Corporate AD" required>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-9">
                    <label class="form-label">Host <span class="text-danger">*</span></label>
                    <input type="text" name="host" class="form-control" value="{{ old('host', $server->host ?? '') }}" placeholder="ldap.example.com" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Port <span class="text-danger">*</span></label>
                    <input type="number" name="port" class="form-control" value="{{ old('port', $server->port ?? 389) }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Base DN <span class="text-danger">*</span></label>
                <input type="text" name="base_dn" class="form-control" value="{{ old('base_dn', $server->base_dn ?? '') }}" placeholder="dc=example,dc=com" required>
            </div>

            <div class="mb-3">
                <label class="form-label">User Filter <span class="text-danger">*</span></label>
                <input type="text" name="user_filter" class="form-control" value="{{ old('user_filter', $server->user_filter ?? 'uid=%s') }}" placeholder="uid=%s or (sAMAccountName=%s)" required>
                <div class="form-text">Use %s as a placeholder for the username used during login.</div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Bind DN (RootDN) (Optional)</label>
                    <input type="text" name="bind_dn" class="form-control" value="{{ old('bind_dn', $server->bind_dn ?? '') }}" placeholder="uid=zimbra,cn=admins,cn=zimbra">
                    <div class="form-text">Leave empty for anonymous bind. GLPI calls this "RootDN".</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Bind Password (Optional)</label>
                    <input type="password" name="bind_password" class="form-control" value="{{ old('bind_password', $server->bind_password ?? '') }}">
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="active" class="form-check-input" id="active" value="1" {{ old('active', $server->active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="active">Active</label>
            </div>

            <hr>
            <div class="d-flex justify-content-end gap-2">
                @if(isset($server))
                <a href="{{ route('admin.ldap.test', $server) }}" class="btn btn-info text-white">
                    <i class="bi bi-plug me-1"></i>Test Connection
                </a>
                @endif
                <a href="{{ route('admin.ldap.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Server</button>
            </div>
        </form>
    </div>
</div>
@endsection
