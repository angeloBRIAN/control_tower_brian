@extends('layouts.app')

@section('title', 'Edit Vehicle - ' . $vehicle->plate_number)

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
            <li class="breadcrumb-item"><a href="{{ route('vehicles.show', $vehicle) }}">{{ $vehicle->plate_number }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
    <h1><i class="bi bi-pencil me-2"></i>Edit Vehicle</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('vehicles.update', $vehicle) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Plate Number <span class="text-danger">*</span></label>
                    <input type="text" name="plate_number" class="form-control @error('plate_number') is-invalid @enderror" value="{{ old('plate_number', $vehicle->plate_number) }}" required>
                    @error('plate_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-control" value="{{ old('model', $vehicle->model) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Year</label>
                    <input type="text" name="year" class="form-control" value="{{ old('year', $vehicle->year) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">VIN</label>
                    <input type="text" name="vin" class="form-control" value="{{ old('vin', $vehicle->vin) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $vehicle->customer_name) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Customer Phone</label>
                    <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', $vehicle->customer_phone) }}">
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_in_workshop" class="form-check-input" id="is_in_workshop" value="1" {{ old('is_in_workshop', $vehicle->is_in_workshop) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_in_workshop">Currently in Workshop</label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between">
                <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                </form>
                <div class="d-flex gap-2">
                    <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Update Vehicle</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
