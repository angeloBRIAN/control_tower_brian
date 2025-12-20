@extends('layouts.app')

@section('title', isset($dropdown) ? 'Edit Option' : 'Add Option')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('admin.dropdowns.index', ['type' => $currentType]) }}">Dropdown Options</a></li>
            <li class="breadcrumb-item active">{{ isset($dropdown) ? 'Edit' : 'Add' }} {{ $types[$currentType] ?? $currentType }}</li>
        </ol>
    </nav>
    <h1>{{ isset($dropdown) ? 'Edit' : 'Add' }} {{ $types[$currentType] ?? $currentType }} Option</h1>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ isset($dropdown) ? route('admin.dropdowns.update', $dropdown) : route('admin.dropdowns.store') }}" method="POST">
                    @csrf
                    @if(isset($dropdown))
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                @foreach($types as $typeKey => $typeLabel)
                                    <option value="{{ $typeKey }}" {{ ($dropdown->type ?? $currentType) === $typeKey ? 'selected' : '' }}>
                                        {{ $typeLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" 
                                   value="{{ old('sort_order', $dropdown->sort_order ?? 0) }}" min="0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Label <span class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control" required
                                   value="{{ old('label', $dropdown->label ?? '') }}"
                                   placeholder="e.g., In Progress">
                            <small class="text-muted">Display name shown to users</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Value <span class="text-danger">*</span></label>
                            <input type="text" name="value" class="form-control" required
                                   value="{{ old('value', $dropdown->value ?? '') }}"
                                   placeholder="e.g., in_progress">
                            <small class="text-muted">Stored in database (lowercase, underscores)</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Icon</label>
                            <select name="icon" class="form-select">
                                @foreach($icons as $iconKey => $iconLabel)
                                    <option value="{{ $iconKey }}" {{ ($dropdown->icon ?? '') === $iconKey ? 'selected' : '' }}>
                                        {{ $iconLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <select name="color" class="form-select">
                                @foreach($colors as $colorKey => $colorLabel)
                                    <option value="{{ $colorKey }}" {{ ($dropdown->color ?? 'secondary') === $colorKey ? 'selected' : '' }}>
                                        {{ $colorLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                                       {{ old('is_active', $dropdown->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>{{ isset($dropdown) ? 'Update' : 'Create' }}
                        </button>
                        <a href="{{ route('admin.dropdowns.index', ['type' => $currentType]) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Preview</div>
            <div class="card-body text-center">
                <div class="py-3">
                    <i class="bi bi-{{ $dropdown->icon ?? 'question-circle' }} fs-1 text-{{ $dropdown->color ?? 'secondary' }}"></i>
                    <h5 class="mt-2 text-{{ $dropdown->color ?? 'secondary' }}">{{ $dropdown->label ?? 'Label' }}</h5>
                    <span class="badge bg-{{ $dropdown->color ?? 'secondary' }}">{{ $dropdown->value ?? 'value' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
