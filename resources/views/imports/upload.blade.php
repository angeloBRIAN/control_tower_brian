@extends('layouts.app')

@section('title', 'Upload Data')

@section('content')
<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center; flex-direction: column;">
    <div class="spinner-border text-light" style="width: 4rem; height: 4rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="text-light mt-3 fs-5">Importing data, please wait...</div>
    <div class="text-light mt-2 small">This may take several minutes for large files</div>
</div>

<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('imports.index') }}">Imports</a></li>
            <li class="breadcrumb-item active">Upload</li>
        </ol>
    </nav>
    <h1><i class="bi bi-file-earmark-arrow-up me-2"></i>Upload Data</h1>
</div>

<div class="row g-4">
<div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-clipboard-check me-2"></i>Import Progress Data
            </div>
            <div class="card-body">
                <p class="text-muted">Import job progress data from PROGRES JOB file. This will create new jobs or update existing ones.</p>
                <form action="{{ route('imports.preview') }}" method="POST" enctype="multipart/form-data" class="import-form">
                    @csrf
                    <input type="hidden" name="import_type" value="progress">

                    <div class="mb-3">
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.ods,.csv" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-eye me-1"></i>Preview
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="directImport(this.form, 'progress')" title="Skip preview">
                            <i class="bi bi-lightning"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-exclamation-triangle me-2"></i>Import Uninvoiced Data
            </div>
            <div class="card-body">
                <p class="text-muted">Import uninvoiced job report from DMS (uiws.xls). This will merge data with existing jobs.</p>
                <form action="{{ route('imports.uninvoiced') }}" method="POST" enctype="multipart/form-data" class="import-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Franchise <span class="text-danger">*</span></label>
                        <select name="franchise" class="form-select" required>
                            <option value="PC">PC - Passenger Car</option>
                            <option value="CV">CV - Commercial Vehicle</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.ods,.csv" required>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-upload me-1"></i>Import Uninvoiced
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <i class="bi bi-check-circle me-2"></i>Import Invoiced Data
            </div>
            <div class="card-body">
                <p class="text-muted">Import invoiced job data (INV sheet). This will mark matching jobs as invoiced.</p>
                <form action="{{ route('imports.invoiced') }}" method="POST" enctype="multipart/form-data" class="import-form">
                    @csrf
                     <div class="mb-3">
                        <label class="form-label">Franchise <span class="text-danger">*</span></label>
                        <select name="franchise" class="form-select" required>
                            <option value="PC">PC - Passenger Car</option>
                            <option value="CV">CV - Commercial Vehicle</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.ods,.csv" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-upload me-1"></i>Import Invoiced
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-info-circle me-2"></i>Import Instructions
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Franchise Types</h6>
                <ul>
                    <li><strong>PC (Passenger Car):</strong> Private vehicles, sedans, hatchbacks, SUVs</li>
                    <li><strong>CV (Commercial Vehicle):</strong> Trucks, buses, commercial fleet vehicles</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Supported Formats</h6>
                <p>Excel (.xlsx, .xls), OpenDocument (.ods), CSV (.csv)</p>
            </div>
        </div>
        
        <h6>Special Sheets (Auto-Detected)</h6>
        <ul>
            <li><strong>BOOKING 2025:</strong> Imported to Bookings table</li>
            <li><strong>PRE DELIVERY INSPECTION:</strong> Imported to PDI Records table</li>
            <li><strong>JADWAL TOWING STOORING:</strong> Imported to Towing Records table</li>
        </ul>
        
        <div class="alert alert-info mb-0">
            <i class="bi bi-lightbulb me-2"></i>The importer will try to match common Indonesian and DMS column names automatically.
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.import-form');
    const overlay = document.getElementById('loadingOverlay');
    
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            overlay.style.display = 'flex';
        });
    });
});

// Direct import (skip preview)
function directImport(form, importType) {
    const routeMap = {
        'progress': '{{ route("imports.progress") }}',
        'uninvoiced': '{{ route("imports.uninvoiced") }}',
        'invoiced': '{{ route("imports.invoiced") }}'
    };
    
    if (routeMap[importType]) {
        form.action = routeMap[importType];
        form.submit();
    }
}
</script>
@endpush
@endsection
