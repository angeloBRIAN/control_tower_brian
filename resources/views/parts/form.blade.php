@extends('layouts.app')

@section('title', isset($partOrder) ? 'Edit Part Order' : 'Add Part Order')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam me-2"></i>
                        {{ isset($partOrder) ? 'Edit Part Order' : 'Add Part Order' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($partOrder) ? route('part-orders.update', $partOrder) : route('part-orders.store') }}" 
                          method="POST">
                        @csrf
                        @if(isset($partOrder))
                            @method('PUT')
                        @endif

                        <!-- Job Selection (only for new orders) -->
                        @if(!isset($partOrder))
                        <div class="mb-3">
                            <label for="job_id" class="form-label">Job <span class="text-danger">*</span></label>
                            @if(isset($job))
                                <input type="hidden" name="job_id" value="{{ $job->id }}">
                                <div class="form-control bg-light">
                                    <strong>{{ $job->job_number }}</strong> - {{ $job->customer_name ?? 'No customer' }}
                                    <br><small class="text-muted">{{ $job->plate_number }}</small>
                                </div>
                            @else
                                <select name="job_id" id="job_id" class="form-select @error('job_id') is-invalid @enderror" required>
                                    <option value="">Select a job that needs parts...</option>
                                    @forelse($jobs as $jobOption)
                                        <option value="{{ $jobOption->id }}" {{ old('job_id') == $jobOption->id ? 'selected' : '' }}>
                                            {{ $jobOption->job_number }} - {{ $jobOption->customer_name ?? 'No customer' }} ({{ $jobOption->plate_number }})
                                        </option>
                                    @empty
                                        <option value="" disabled>No jobs with "Needs Parts" found</option>
                                    @endforelse
                                </select>
                                <div class="form-text">Only showing uninvoiced jobs marked as "Needs Parts"</div>
                                @error('job_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                        </div>
                        @else
                        <div class="mb-3">
                            <label class="form-label">Job</label>
                            <div class="form-control bg-light">
                                <a href="{{ route('jobs.show', $partOrder->job_id) }}">
                                    <strong>{{ $partOrder->job->job_number }}</strong>
                                </a>
                                - {{ $partOrder->job->customer_name ?? 'No customer' }}
                            </div>
                        </div>
                        @endif


                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rq" class="form-label">RQ (Requisition) <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('rq') is-invalid @enderror" 
                                           id="rq" 
                                           name="rq" 
                                           value="{{ old('rq', $partOrder->rq ?? '') }}"
                                           required>
                                    @error('rq')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="no_order_part" class="form-label">No. Order Part</label>
                                    <input type="text" 
                                           class="form-control @error('no_order_part') is-invalid @enderror" 
                                           id="no_order_part" 
                                           name="no_order_part" 
                                           value="{{ old('no_order_part', $partOrder->no_order_part ?? '') }}">
                                    @error('no_order_part')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="order_date" class="form-label">Order Date</label>
                                    <input type="date" 
                                           class="form-control bg-light" 
                                           id="order_date" 
                                           name="order_date" 
                                           value="{{ old('order_date', isset($partOrder) && $partOrder->order_date ? $partOrder->order_date->format('Y-m-d') : date('Y-m-d')) }}"
                                           readonly>
                                    <div class="form-text text-muted small">Auto-set when created</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="expected_date" class="form-label">Expected Date <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control date-shortcuts @error('expected_date') is-invalid @enderror" 
                                           id="expected_date" 
                                           name="expected_date" 
                                           value="{{ old('expected_date', isset($partOrder) && $partOrder->expected_date ? $partOrder->expected_date->format('Y-m-d') : '') }}"
                                           required>
                                    <div class="form-text text-muted small">
                                        <kbd>t</kbd> today | <kbd>→</kbd> +1d | <kbd>←</kbd> -1d | <kbd>↑</kbd> +7d | <kbd>↓</kbd> -7d
                                    </div>
                                    @error('expected_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Status Dates</label>
                                    @if(isset($partOrder))
                                    <div class="small">
                                        <div class="d-flex justify-content-between py-1 border-bottom">
                                            <span class="text-muted">Ready:</span>
                                            <span class="fw-semibold">{{ $partOrder->ready_date?->format('d/m/Y') ?? '-' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between py-1">
                                            <span class="text-muted">Received:</span>
                                            <span class="fw-semibold">{{ $partOrder->received_date?->format('d/m/Y') ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="form-text text-muted small">Auto-set on Kanban status change</div>
                                    @else
                                    <div class="form-control-plaintext text-muted small">-</div>
                                    @endif
                                </div>
                            </div>
                        </div>


                        @if(isset($partOrder))
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach($statuses as $key => $info)
                                    <option value="{{ $key }}" {{ old('status', $partOrder->status) === $key ? 'selected' : '' }}>
                                        {{ $info['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3">{{ old('notes', $partOrder->notes ?? '') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ isset($partOrder) ? 'Update' : 'Create' }} Part Order
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Date keyboard shortcuts for fields with .date-shortcuts class
    document.querySelectorAll('.date-shortcuts').forEach(input => {
        input.addEventListener('keydown', function(e) {
            // Get current date or today if empty
            let currentDate = this.value ? new Date(this.value) : new Date();
            
            let handled = false;
            
            switch(e.key) {
                case 't':
                case 'T':
                    // t = Today
                    currentDate = new Date();
                    handled = true;
                    break;
                case 'ArrowRight':
                    // → = +1 day
                    currentDate.setDate(currentDate.getDate() + 1);
                    handled = true;
                    break;
                case 'ArrowLeft':
                    // ← = -1 day
                    currentDate.setDate(currentDate.getDate() - 1);
                    handled = true;
                    break;
                case 'ArrowUp':
                    // ↑ = +7 days (next week)
                    currentDate.setDate(currentDate.getDate() + 7);
                    handled = true;
                    break;
                case 'ArrowDown':
                    // ↓ = -7 days (previous week)
                    currentDate.setDate(currentDate.getDate() - 7);
                    handled = true;
                    break;
            }
            
            if (handled) {
                e.preventDefault();
                // Format as YYYY-MM-DD
                const year = currentDate.getFullYear();
                const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                const day = String(currentDate.getDate()).padStart(2, '0');
                this.value = `${year}-${month}-${day}`;
            }
        });
    });
});
</script>
@endpush
