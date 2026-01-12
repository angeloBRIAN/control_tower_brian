@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="page-header mb-4">
    <h1><i class="bi bi-person-circle me-2"></i>My Profile</h1>
    <p class="text-muted mb-0">Manage your account settings</p>
</div>

<div class="row">
    <!-- Profile Info Card -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-person me-2"></i>Profile Information
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar-placeholder bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px; font-size: 2rem;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>
                
                <table class="table table-sm">
                    <tr>
                        <th class="text-muted" style="width: 40%;">Name</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Email</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Auth Source</th>
                        <td>
                            @if(!$user->auth_source || $user->auth_source === 'local')
                                <span class="badge bg-secondary"><i class="bi bi-database me-1"></i>Internal</span>
                            @else
                                <span class="badge bg-primary"><i class="bi bi-server me-1"></i>LDAP</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Role</th>
                        <td>
                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'manager' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Member Since</th>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Last Login</th>
                        <td>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Change Password Card -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-warning">
                <i class="bi bi-key me-2"></i>Change Password
            </div>
            <div class="card-body">
                @if($user->auth_source && $user->auth_source !== 'local')
                    <!-- LDAP User - Cannot change password -->
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>LDAP User</strong><br>
                        Your account is managed through LDAP/Active Directory. 
                        To change your password, please contact your IT administrator or use your organization's password management system.
                    </div>
                @else
                    <!-- Internal User - Show password change form -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">
                                <i class="bi bi-lock me-1"></i>Current Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" 
                                   name="current_password" 
                                   required 
                                   autocomplete="current-password">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-key me-1"></i>New Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required 
                                   minlength="8"
                                   autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimum 8 characters</div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">
                                <i class="bi bi-key-fill me-1"></i>Confirm New Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required 
                                   minlength="8"
                                   autocomplete="new-password">
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                <i class="bi bi-info-circle me-1"></i>
                                You will remain logged in after changing your password.
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-lg me-1"></i>Update Password
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <!-- Security Tips -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <i class="bi bi-shield-check me-2"></i>Security Tips
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Use a strong password with at least 8 characters</li>
                    <li>Include a mix of uppercase, lowercase, numbers, and symbols</li>
                    <li>Don't reuse passwords from other websites</li>
                    <li>Never share your password with anyone</li>
                    <li>Log out when using shared computers</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
