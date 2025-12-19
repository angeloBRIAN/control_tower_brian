@extends('layouts.app')

@section('title', 'Data Cleanup')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-trash3 me-2"></i>Data Cleanup</h1>
        <p class="text-muted mb-0">Clean up transactional data while keeping master data and users</p>
    </div>
</div>



<div class="row">
    <div class="col-lg-8">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle me-2"></i>Warning: This action is irreversible!
            </div>
            <div class="card-body">
                <form action="{{ route('admin.data-cleanup.execute') }}" method="POST" id="cleanupForm">
                    @csrf
                    
                    <div class="alert alert-warning">
                        <strong><i class="bi bi-shield-exclamation me-2"></i>Protected Data (will NOT be deleted):</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Users</strong> - All user accounts</li>
                            <li><strong>Foremen</strong> - Master data</li>
                            <li><strong>Service Advisors</strong> - Master data</li>
                            <li><strong>LDAP Servers</strong> - Configuration</li>
                            <li><strong>Saved Reports</strong> - Report configurations</li>
                        </ul>
                    </div>

                    <h5 class="mb-3">Select data to delete:</h5>
                    
                    <div class="row g-3 mb-4">
                        @foreach([
                            'jobs' => ['Jobs', 'bi-briefcase', 'danger'],
                            'bookings' => ['Bookings', 'bi-calendar-check', 'primary'],
                            'pdi_records' => ['PDI Records', 'bi-clipboard-check', 'success'],
                            'towing_records' => ['Towing Records', 'bi-truck', 'info'],
                            'vehicles' => ['Vehicles', 'bi-car-front', 'warning'],
                            'remarks' => ['Remarks/Comments', 'bi-chat-text', 'secondary'],
                            'imports' => ['Import History', 'bi-upload', 'dark'],
                            'audit_logs' => ['Audit Logs', 'bi-journal-text', 'secondary'],
                        ] as $table => $info)
                        <div class="col-md-6">
                            <div class="form-check card p-3 border">
                                <input class="form-check-input me-2" type="checkbox" name="tables[]" value="{{ $table }}" id="table_{{ $table }}">
                                <label class="form-check-label d-flex align-items-center justify-content-between w-100" for="table_{{ $table }}">
                                    <span>
                                        <i class="bi {{ $info[1] }} me-2 text-{{ $info[2] }}"></i>{{ $info[0] }}
                                    </span>
                                    <span class="badge bg-{{ $info[2] }}">{{ number_format($counts[$table]) }}</span>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAll">Select All</button>
                        <span class="text-muted">Total records: <strong>{{ number_format($totalRecords) }}</strong></span>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-danger">Type "DELETE ALL DATA" to confirm:</label>
                        <input type="text" class="form-control" name="confirmation" id="confirmInput" 
                               placeholder="DELETE ALL DATA" autocomplete="off">
                        <small class="text-muted">This confirmation is required to prevent accidental deletion.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger" id="deleteBtn" disabled>
                            <i class="bi bi-trash me-1"></i>Delete Selected Data
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card bg-light">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Information
            </div>
            <div class="card-body">
                <h6>What this does:</h6>
                <ul class="small">
                    <li>Permanently deletes selected transactional data</li>
                    <li>Frees up database space</li>
                    <li>Useful for testing or fresh starts</li>
                </ul>
                
                <h6>What this preserves:</h6>
                <ul class="small mb-0">
                    <li>User accounts and preferences</li>
                    <li>Master data (Foremen, SA)</li>
                    <li>System configuration</li>
                </ul>
            </div>
        </div>

        <div class="card mt-3 border-info">
            <div class="card-header bg-info text-white">
                <i class="bi bi-lightbulb me-2"></i>Tip
            </div>
            <div class="card-body small">
                <p class="mb-0">Before cleaning data, consider exporting important records using the <a href="{{ route('reports.builder') }}">Report Builder</a>.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmInput = document.getElementById('confirmInput');
    const deleteBtn = document.getElementById('deleteBtn');
    const form = document.getElementById('cleanupForm');
    const selectAllBtn = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('input[name="tables[]"]');

    // Enable/disable delete button based on confirmation
    confirmInput.addEventListener('input', function() {
        const hasChecked = document.querySelector('input[name="tables[]"]:checked');
        deleteBtn.disabled = this.value !== 'DELETE ALL DATA' || !hasChecked;
    });

    // Also check when checkboxes change
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const hasChecked = document.querySelector('input[name="tables[]"]:checked');
            deleteBtn.disabled = confirmInput.value !== 'DELETE ALL DATA' || !hasChecked;
        });
    });

    // Select all toggle
    selectAllBtn.addEventListener('click', function() {
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
        this.textContent = allChecked ? 'Select All' : 'Deselect All';
        
        // Trigger change event
        confirmInput.dispatchEvent(new Event('input'));
    });

    // Final confirmation before submit
    form.addEventListener('submit', function(e) {
        const selected = document.querySelectorAll('input[name="tables[]"]:checked');
        const count = selected.length;
        
        if (!confirm(`Are you absolutely sure?\n\nThis will permanently delete data from ${count} table(s).\n\nThis action CANNOT be undone!`)) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
@endsection
