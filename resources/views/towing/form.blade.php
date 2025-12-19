@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-truck me-2"></i>{{ isset($towing) ? 'Edit Towing Record' : 'Add Towing Record' }}</h2>
        <a href="{{ route('towing-records.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ isset($towing) ? route('towing-records.update', $towing) : route('towing-records.store') }}" method="POST">
                @csrf
                @if(isset($towing)) @method('PUT') @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="plate_number" class="form-label">Plate Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('plate_number') is-invalid @enderror" id="plate_number" name="plate_number" value="{{ old('plate_number', $towing->plate_number ?? '') }}" required>
                        @error('plate_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="customer_name" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ old('customer_name', $towing->customer_name ?? '') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="scheduled_date" class="form-label">Scheduled Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('scheduled_date') is-invalid @enderror" id="scheduled_date" name="scheduled_date" value="{{ old('scheduled_date', isset($towing) ? $towing->scheduled_date->format('Y-m-d') : '') }}" required>
                        @error('scheduled_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="job_type" class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="job_type" name="job_type" required>
                            <option value="towing" {{ old('job_type', $towing->job_type ?? '') == 'towing' ? 'selected' : '' }}>Towing</option>
                            <option value="storing" {{ old('job_type', $towing->job_type ?? '') == 'storing' ? 'selected' : '' }}>Storing</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="scheduled" {{ old('status', $towing->status ?? '') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="in_progress" {{ old('status', $towing->status ?? '') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status', $towing->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $towing->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="pickup_location" class="form-label">Pickup Location</label>
                    <input type="text" class="form-control" id="pickup_location" name="pickup_location" value="{{ old('pickup_location', $towing->pickup_location ?? '') }}">
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes / Remarks</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $towing->notes ?? '') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> {{ isset($towing) ? 'Update' : 'Create' }} Towing Record
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
