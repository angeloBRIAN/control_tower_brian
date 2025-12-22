@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar-check me-2"></i>{{ isset($booking) ? 'Edit Booking' : 'Add Booking' }}</h2>
        <a href="{{ route('bookings.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ isset($booking) ? route('bookings.update', $booking) : route('bookings.store') }}" method="POST">
                @csrf
                @if(isset($booking)) @method('PUT') @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="plate_number" class="form-label">Plate Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('plate_number') is-invalid @enderror" id="plate_number" name="plate_number" value="{{ old('plate_number', $booking->plate_number ?? '') }}" required>
                            <span class="input-group-text" id="plateStatus"><i class="bi bi-search"></i></span>
                        </div>
                        <small class="text-muted" id="plateHint">Enter plate number to lookup customer</small>
                        @error('plate_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="customer_name" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ old('customer_name', $booking->customer_name ?? '') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="booking_date" class="form-label">Booking Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('booking_date') is-invalid @enderror" id="booking_date" name="booking_date" value="{{ old('booking_date', isset($booking) ? $booking->booking_date->format('Y-m-d') : '') }}" required>
                        @error('booking_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="service_type" class="form-label">Service Type</label>
                        <input type="text" class="form-control" id="service_type" name="service_type" value="{{ old('service_type', $booking->service_type ?? '') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending" {{ old('status', $booking->status ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ old('status', $booking->status ?? '') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="completed" {{ old('status', $booking->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $booking->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="wip" class="form-label">WIP Number</label>
                        <input type="text" class="form-control" id="wip" name="wip" value="{{ old('wip', $booking->wip ?? '') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="foreman" class="form-label">Foreman</label>
                        <input type="text" class="form-control" id="foreman" name="foreman" value="{{ old('foreman', $booking->foreman ?? '') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="service_advisor" class="form-label">Service Advisor</label>
                        <input type="text" class="form-control" id="service_advisor" name="service_advisor" value="{{ old('service_advisor', $booking->service_advisor ?? '') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes / Remarks</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $booking->notes ?? '') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> {{ isset($booking) ? 'Update' : 'Create' }} Booking
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const plateInput = document.getElementById('plate_number');
    const customerInput = document.getElementById('customer_name');
    const plateStatus = document.getElementById('plateStatus');
    const plateHint = document.getElementById('plateHint');
    let lookupTimeout;
    
    plateInput.addEventListener('input', function() {
        clearTimeout(lookupTimeout);
        const plate = this.value.trim();
        
        if (plate.length < 3) {
            plateStatus.innerHTML = '<i class="bi bi-search"></i>';
            plateHint.textContent = 'Enter plate number to lookup customer';
            plateHint.className = 'text-muted';
            return;
        }
        
        plateStatus.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        lookupTimeout = setTimeout(() => {
            fetch(`/api/vehicles/lookup?plate=${encodeURIComponent(plate)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.found) {
                        plateStatus.innerHTML = '<i class="bi bi-check-circle text-success"></i>';
                        plateHint.textContent = 'Vehicle found!';
                        plateHint.className = 'text-success';
                        if (data.customer_name && !customerInput.value) {
                            customerInput.value = data.customer_name;
                        }
                    } else {
                        plateStatus.innerHTML = '<i class="bi bi-plus-circle text-primary"></i>';
                        plateHint.textContent = 'New vehicle';
                        plateHint.className = 'text-primary';
                    }
                })
                .catch(() => {
                    plateStatus.innerHTML = '<i class="bi bi-search"></i>';
                });
        }, 400);
    });
});
</script>
@endpush
@endsection

