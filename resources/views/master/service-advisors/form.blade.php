@extends('layouts.app')

@section('title', isset($serviceAdvisor) ? 'Edit Service Advisor' : 'Add Service Advisor')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('service-advisors.index') }}">Service Advisors</a></li>
                <li class="breadcrumb-item active">{{ isset($serviceAdvisor) ? 'Edit' : 'Create' }}</li>
            </ol>
        </nav>
        <h1><i class="bi bi-person-badge me-2"></i>{{ isset($serviceAdvisor) ? 'Edit Service Advisor' : 'Add Service Advisor' }}</h1>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form action="{{ isset($serviceAdvisor) ? route('service-advisors.update', $serviceAdvisor) : route('service-advisors.store') }}" method="POST">
                    @csrf
                    @if(isset($serviceAdvisor))
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $serviceAdvisor->name ?? '') }}" required>
                        @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Franchise (Optional)</label>
                        <select name="franchise" class="form-select">
                            <option value="">-- Select Franchise --</option>
                            <option value="PC" {{ (old('franchise', $serviceAdvisor->franchise ?? '') == 'PC') ? 'selected' : '' }}>PC - Passenger Car</option>
                            <option value="CV" {{ (old('franchise', $serviceAdvisor->franchise ?? '') == 'CV') ? 'selected' : '' }}>CV - Commercial Vehicle</option>
                        </select>
                         <div class="form-text">Used to auto-detect franchise during imports.</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="activeSwitch" name="active" value="1" {{ (old('active', $serviceAdvisor->active ?? true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activeSwitch">Active Status</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link to System User (Optional)</label>
                        <select name="user_id" class="form-select">
                            <option value="">-- No Linked User --</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $serviceAdvisor->user_id ?? '') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Link this service advisor to a system user with the "SA" role for comment attribution.</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('service-advisors.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
