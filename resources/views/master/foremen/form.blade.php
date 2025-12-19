@extends('layouts.app')

@section('title', isset($foreman) ? 'Edit Foreman' : 'Add Foreman')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('foremen.index') }}">Foremen</a></li>
                <li class="breadcrumb-item active">{{ isset($foreman) ? 'Edit' : 'Create' }}</li>
            </ol>
        </nav>
        <h1><i class="bi bi-tools me-2"></i>{{ isset($foreman) ? 'Edit Foreman' : 'Add Foreman' }}</h1>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form action="{{ isset($foreman) ? route('foremen.update', $foreman) : route('foremen.store') }}" method="POST">
                    @csrf
                    @if(isset($foreman))
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $foreman->name ?? '') }}" required>
                        @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Franchise (Optional)</label>
                        <select name="franchise" class="form-select">
                            <option value="">-- Select Franchise --</option>
                            <option value="PC" {{ (old('franchise', $foreman->franchise ?? '') == 'PC') ? 'selected' : '' }}>PC - Passenger Car</option>
                            <option value="CV" {{ (old('franchise', $foreman->franchise ?? '') == 'CV') ? 'selected' : '' }}>CV - Commercial Vehicle</option>
                        </select>
                         <div class="form-text">Used to auto-detect franchise during imports.</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="activeSwitch" name="active" value="1" {{ (old('active', $foreman->active ?? true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activeSwitch">Active Status</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link to System User (Optional)</label>
                        <select name="user_id" class="form-select">
                            <option value="">-- No Linked User --</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $foreman->user_id ?? '') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Link this foreman to a system user with the "Foreman" role for comment attribution.</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('foremen.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
