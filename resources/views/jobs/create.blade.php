@extends('layouts.app')

@section('title', 'Add New Job')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('jobs.index') }}">Jobs</a></li>
            <li class="breadcrumb-item active">Add New</li>
        </ol>
    </nav>
    <h1><i class="bi bi-plus-circle me-2"></i>Add New Job</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('jobs.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Job Number <span class="text-danger">*</span></label>
                    <input type="text" name="job_number" class="form-control @error('job_number') is-invalid @enderror" value="{{ old('job_number') }}" required>
                    @error('job_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Franchise <span class="text-danger">*</span></label>
                    <select name="franchise" class="form-select @error('franchise') is-invalid @enderror" required>
                        <option value="PC" {{ old('franchise') == 'PC' ? 'selected' : '' }}>PC - Passenger Car</option>
                        <option value="CV" {{ old('franchise') == 'CV' ? 'selected' : '' }}>CV - Commercial Vehicle</option>
                    </select>
                    @error('franchise')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Plate Number <span class="text-danger">*</span></label>
                    <input type="text" name="plate_number" class="form-control @error('plate_number') is-invalid @enderror" value="{{ old('plate_number') }}" required>
                    @error('plate_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Chassis Number</label>
                    <input type="text" name="chassis_number" class="form-control" value="{{ old('chassis_number') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Service Advisor</label>
                    <input type="text" name="service_advisor" class="form-control" value="{{ old('service_advisor') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Technician</label>
                    <input type="text" name="technician" class="form-control" value="{{ old('technician') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Type</label>
                    <input type="text" name="payment_type" class="form-control" value="{{ old('payment_type') }}" placeholder="CASH, AR, etc">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Job Type</label>
                    <select name="job_type" class="form-select">
                        <option value="">-- Select Type --</option>
                        <option value="regular" {{ old('job_type') == 'regular' ? 'selected' : '' }}>Regular Service</option>
                        <option value="pdi" {{ old('job_type') == 'pdi' ? 'selected' : '' }}>PDI</option>
                        <option value="booking" {{ old('job_type') == 'booking' ? 'selected' : '' }}>Booking</option>
                        <option value="towing" {{ old('job_type') == 'towing' ? 'selected' : '' }}>Towing</option>
                        <option value="body_repair" {{ old('job_type') == 'body_repair' ? 'selected' : '' }}>Body Repair</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Job Date</label>
                    <input type="date" name="job_date" class="form-control" value="{{ old('job_date', date('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Promise Date</label>
                    <input type="date" name="promise_date" class="form-control" value="{{ old('promise_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Amount (Rp)</label>
                    <input type="number" name="estimated_amount" class="form-control" value="{{ old('estimated_amount') }}" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Work Status</label>
                    <select name="work_status" class="form-select">
                        <option value="">-- Select Status --</option>
                        <option value="pending" {{ old('work_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ old('work_status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="waiting_parts" {{ old('work_status') == 'waiting_parts' ? 'selected' : '' }}>Waiting Parts</option>
                        <option value="waiting_approval" {{ old('work_status') == 'waiting_approval' ? 'selected' : '' }}>Waiting Approval</option>
                        <option value="completed" {{ old('work_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Initial Remark</label>
                    <input type="text" name="initial_remark" class="form-control" placeholder="Optional initial remark" value="{{ old('initial_remark') }}">
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('jobs.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Job</button>
            </div>
        </form>
    </div>
</div>
@endsection
