@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clipboard-check me-2"></i>{{ isset($pdi) ? 'Edit PDI Record' : 'Add PDI Record' }}</h2>
        <a href="{{ route('pdi-records.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ isset($pdi) ? route('pdi-records.update', $pdi) : route('pdi-records.store') }}" method="POST">
                @csrf
                @if(isset($pdi)) @method('PUT') @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="plate_number" class="form-label">Plate Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('plate_number') is-invalid @enderror" id="plate_number" name="plate_number" value="{{ old('plate_number', $pdi->plate_number ?? '') }}" required>
                        @error('plate_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="model" class="form-label">Model</label>
                        <input type="text" class="form-control" id="model" name="model" value="{{ old('model', $pdi->model ?? '') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="pdi_date" class="form-label">PDI Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('pdi_date') is-invalid @enderror" id="pdi_date" name="pdi_date" value="{{ old('pdi_date', isset($pdi) ? $pdi->pdi_date->format('Y-m-d') : '') }}" required>
                        @error('pdi_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="technician" class="form-label">Foreman</label>
                        <input type="text" class="form-control" id="technician" name="technician" value="{{ old('technician', $pdi->technician ?? '') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending" {{ old('status', $pdi->status ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ old('status', $pdi->status ?? '') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status', $pdi->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="vin" class="form-label">VIN / Chassis No</label>
                        <input type="text" class="form-control" id="vin" name="vin" value="{{ old('vin', $pdi->vin ?? '') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="engine_no" class="form-label">Engine No</label>
                        <input type="text" class="form-control" id="engine_no" name="engine_no" value="{{ old('engine_no', $pdi->engine_no ?? '') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="colour" class="form-label">Colour</label>
                        <input type="text" class="form-control" id="colour" name="colour" value="{{ old('colour', $pdi->colour ?? '') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="wip" class="form-label">WIP Number</label>
                        <input type="text" class="form-control" id="wip" name="wip" value="{{ old('wip', $pdi->wip ?? '') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes / Remarks</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $pdi->notes ?? '') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> {{ isset($pdi) ? 'Update' : 'Create' }} PDI Record
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
