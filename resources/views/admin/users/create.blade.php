@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-person-plus me-2"></i>Create Local User</h1>
        <p class="text-muted mb-0">Create a new system user with role assignment</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Users
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-fill me-2"></i>User Details
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                               required minlength="6">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Minimum 6 characters</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">-- Select Role --</option>
                            @foreach($roles as $key => $label)
                                <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check me-1"></i>Create User
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Quick Tips -->
        <div class="card mt-3">
            <div class="card-header bg-info bg-opacity-10">
                <i class="bi bi-lightbulb me-2"></i>Role Guide
            </div>
            <div class="card-body small">
                <ul class="mb-0">
                    <li><strong>Admin</strong>: Full access to all features</li>
                    <li><strong>Manager</strong>: Can manage jobs, users, and reports</li>
                    <li><strong>Control Tower</strong>: Manages Kanban workflow</li>
                    <li><strong>Finance</strong>: Manages invoicing and payments</li>
                    <li><strong>Sparepart</strong>: Manages parts ordering</li>
                    <li><strong>Service Advisor</strong>: Creates jobs, views assigned work</li>
                    <li><strong>Foreman</strong>: Views and updates job progress</li>
                    <li><strong>Billing</strong>: Views all jobs and Kanban, can only add comments</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
