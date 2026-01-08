@extends('layouts.app')

@section('title', 'DMS Import')

@section('content')
<!-- Enhanced Loading Overlay -->
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(13, 148, 136, 0.95) 0%, rgba(17, 94, 89, 0.95) 100%); z-index: 9999; justify-content: center; align-items: center; flex-direction: column;">
    <div class="loading-container text-center">
        <!-- Animated Spinner -->
        <div class="position-relative mb-4">
            <div class="spinner-grow text-light" style="width: 5rem; height: 5rem; animation-duration: 1s;" role="status"></div>
            <div class="spinner-grow text-light position-absolute" style="width: 5rem; height: 5rem; top: 0; left: 50%; transform: translateX(-50%); animation-delay: 0.3s; opacity: 0.7;" role="status"></div>
            <div class="spinner-grow text-light position-absolute" style="width: 5rem; height: 5rem; top: 0; left: 50%; transform: translateX(-50%); animation-delay: 0.6s; opacity: 0.4;" role="status"></div>
        </div>
        
        <!-- Main Message -->
        <h3 class="text-white mb-3">
            <i class="bi bi-cloud-upload me-2"></i>
            <span id="loadingTitle">Importing Data...</span>
        </h3>
        
        <!-- Animated Status Messages -->
        <div id="loadingStatus" class="text-white-50 fs-5 mb-3" style="min-height: 30px;">
            Preparing import...
        </div>
        
        <!-- Elapsed Time -->
        <div class="text-white-50 mb-4">
            <i class="bi bi-clock me-1"></i>
            Elapsed: <span id="elapsedTime">0:00</span>
        </div>
        
        <!-- Progress Steps -->
        <div class="d-flex justify-content-center gap-2 mb-4">
            <div class="step-dot" id="step1"></div>
            <div class="step-dot" id="step2"></div>
            <div class="step-dot" id="step3"></div>
            <div class="step-dot" id="step4"></div>
            <div class="step-dot" id="step5"></div>
        </div>
        
        <!-- Tip -->
        <div class="text-white-50 small" style="max-width: 400px;">
            <i class="bi bi-info-circle me-1"></i>
            Large files may take several minutes. Please don't close this page.
        </div>
    </div>
</div>

<style>
.step-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    transition: all 0.3s ease;
}
.step-dot.active {
    background: #fff;
    box-shadow: 0 0 10px rgba(255,255,255,0.8);
    transform: scale(1.2);
}
@keyframes pulse-message {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}
#loadingStatus {
    animation: pulse-message 2s ease-in-out infinite;
}
</style>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('imports.index') }}">Imports</a></li>
                <li class="breadcrumb-item active">DMS Import</li>
            </ol>
        </nav>
        <h1><i class="bi bi-cloud-upload me-2"></i>DMS Import</h1>
        <p class="text-muted mb-0">Import customer and vehicle data from DMS Excel files</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('import_results'))
@php $results = session('import_results'); @endphp
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <h6 class="alert-heading mb-2"><i class="bi bi-info-circle me-2"></i>Import Results</h6>
    <div class="d-flex gap-3">
        <span class="badge bg-success fs-6">{{ $results['created'] ?? 0 }} Created</span>
        <span class="badge bg-primary fs-6">{{ $results['updated'] ?? 0 }} Updated</span>
        <span class="badge bg-danger fs-6">{{ $results['errors'] ?? 0 }} Errors</span>
    </div>
    @if(!empty($results['error_messages']))
    <hr>
    <details>
        <summary class="text-muted cursor-pointer">Show errors ({{ count($results['error_messages']) }})</summary>
        <div class="mt-2 small text-muted" style="max-height: 200px; overflow-y: auto;">
            @foreach(array_slice($results['error_messages'], 0, 20) as $msg)
            <div>• {{ $msg }}</div>
            @endforeach
            @if(count($results['error_messages']) > 20)
            <div class="mt-1 fw-bold">... and {{ count($results['error_messages']) - 20 }} more errors</div>
            @endif
        </div>
    </details>
    @endif
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    <!-- Customer Import -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-people me-2"></i>Import Customer Data
            </div>
            <div class="card-body">
                <p class="text-muted">Import customers from DMS Excel file. This will create or update customer records.</p>
                
                <h6>Expected Columns:</h6>
                <ul class="small text-muted mb-3">
                    <li><strong>Magic cust</strong> - Unique customer ID (required)</li>
                    <li>Nama Customer, ADDRESS 1-5</li>
                    <li>Company name, E-mail address, Dept</li>
                </ul>
                
                <form action="{{ route('admin.dms-import.customers') }}" method="POST" enctype="multipart/form-data" class="import-form" data-type="customers">
                    @csrf
                    <div class="mb-3">
                        <input type="file" class="form-control" name="file" accept=".xls,.xlsx" required>
                        <div class="form-text">Max file size: 10MB. Accepted: .xls, .xlsx</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-1"></i>Import Customers
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Vehicle Import -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <i class="bi bi-truck me-2"></i>Import Vehicle Data
            </div>
            <div class="card-body">
                <p class="text-muted">Import vehicles from DMS Excel file. Phone numbers will be synced to linked customers.</p>
                
                <h6>Expected Columns:</h6>
                <ul class="small text-muted mb-3">
                    <li><strong>Magic</strong> - Unique vehicle ID</li>
                    <li><strong>Registration No</strong> - Plate number</li>
                    <li>Model, Variant, Chassis No, Engine No</li>
                    <li>Customer Magic, Phone1-4</li>
                </ul>
                
                <form action="{{ route('admin.dms-import.vehicles') }}" method="POST" enctype="multipart/form-data" class="import-form" data-type="vehicles">
                    @csrf
                    <div class="mb-3">
                        <input type="file" class="form-control" name="file" accept=".xls,.xlsx" required>
                        <div class="form-text">Max file size: 10MB. Accepted: .xls, .xlsx</div>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-upload me-1"></i>Import Vehicles
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import Notes -->
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-info-circle me-2"></i>Import Notes
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Customer Import</h6>
                <ul class="text-muted small">
                    <li>Uses <code>Magic cust</code> to identify existing customers</li>
                    <li>Existing customers will be updated with new data</li>
                    <li>New customers will be created</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Vehicle Import</h6>
                <ul class="text-muted small">
                    <li>Uses <code>Magic</code> or <code>Registration No</code> to identify existing vehicles</li>
                    <li><strong>Existing vehicles preserve their "In Workshop" status</strong></li>
                    <li>New vehicles are set to "Not in Workshop"</li>
                    <li>Phone numbers are synced to linked customers</li>
                    <li>All changes are logged in the audit trail</li>
                </ul>
            </div>
        </div>
        
        <div class="alert alert-warning mt-3 mb-0">
            <i class="bi bi-lightbulb me-2"></i>
            <strong>Tip:</strong> Import customers first, then vehicles. This ensures vehicle-customer links work correctly.
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.import-form');
    const overlay = document.getElementById('loadingOverlay');
    const loadingTitle = document.getElementById('loadingTitle');
    const statusEl = document.getElementById('loadingStatus');
    const elapsedEl = document.getElementById('elapsedTime');
    
    let startTime = null;
    let timerInterval = null;
    let messageInterval = null;
    let stepInterval = null;
    let currentStep = 0;
    
    const customerMessages = [
        'Preparing customer import...',
        'Reading Excel file...',
        'Parsing customer records...',
        'Validating data...',
        'Creating new customers...',
        'Updating existing customers...',
        'Building addresses...',
        'Almost done...',
        'Finalizing import...'
    ];
    
    const vehicleMessages = [
        'Preparing vehicle import...',
        'Reading Excel file...',
        'Parsing vehicle records...',
        'Validating plate numbers...',
        'Creating new vehicles...',
        'Updating existing vehicles...',
        'Syncing customer phones...',
        'Creating audit logs...',
        'Almost done...',
        'Finalizing import...'
    ];
    
    function formatElapsed(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
    
    function startLoadingAnimation(importType) {
        const messages = importType === 'customers' ? customerMessages : vehicleMessages;
        loadingTitle.textContent = importType === 'customers' ? 'Importing Customers...' : 'Importing Vehicles...';
        
        startTime = Date.now();
        let messageIndex = 0;
        
        // Update elapsed time every second
        timerInterval = setInterval(() => {
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            elapsedEl.textContent = formatElapsed(elapsed);
        }, 1000);
        
        // Cycle through status messages every 3 seconds
        messageInterval = setInterval(() => {
            messageIndex = (messageIndex + 1) % messages.length;
            statusEl.textContent = messages[messageIndex];
        }, 3000);
        
        // Animate progress dots
        stepInterval = setInterval(() => {
            for (let i = 1; i <= 5; i++) {
                document.getElementById('step' + i).classList.remove('active');
            }
            currentStep = (currentStep % 5) + 1;
            document.getElementById('step' + currentStep).classList.add('active');
        }, 600);
    }
    
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const importType = form.dataset.type || 'data';
            overlay.style.display = 'flex';
            startLoadingAnimation(importType);
        });
    });
});
</script>
@endpush
@endsection
