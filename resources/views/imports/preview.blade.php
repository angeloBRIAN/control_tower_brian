@extends('layouts.app')

@section('title', 'Import Preview')

@section('content')
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('imports.index') }}">Imports</a></li>
            <li class="breadcrumb-item"><a href="{{ route('imports.upload') }}">Upload</a></li>
            <li class="breadcrumb-item active">Preview</li>
        </ol>
    </nav>
    <h1><i class="bi bi-eye me-2"></i>Import Preview</h1>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-1 fw-bold text-primary">{{ $totalRows }}</div>
                <div class="text-muted">Total Rows</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-1 fw-bold text-success">{{ $validCount }}</div>
                <div class="text-muted">Valid</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-1 fw-bold text-warning">{{ $warningCount }}</div>
                <div class="text-muted">Warnings</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-1 fw-bold text-danger">{{ $errorCount }}</div>
                <div class="text-muted">Errors</div>
            </div>
        </div>
    </div>
</div>

<!-- File Info -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <strong>File:</strong> {{ $fileName }}
            </div>
            <div class="col-md-4">
                <strong>Import Type:</strong> 
                <span class="badge bg-{{ $importType === 'progress' ? 'primary' : ($importType === 'uninvoiced' ? 'warning' : 'success') }}">
                    {{ ucfirst($importType) }}
                </span>
            </div>
            @if($franchise)
            <div class="col-md-4">
                <strong>Franchise:</strong> {{ $franchise }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Validation Alert -->
@if($errorCount > 0)
<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>{{ $errorCount }} rows have errors</strong> and will be skipped during import. 
    Review the preview below and fix issues in your file before importing.
</div>
@endif

@if($warningCount > 0)
<div class="alert alert-warning">
    <i class="bi bi-exclamation-circle me-2"></i>
    <strong>{{ $warningCount }} rows have warnings.</strong> 
    These will be processed but may overwrite existing data.
</div>
@endif

<!-- Preview Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-table me-2"></i>Preview (First {{ count($previewRows) }} of {{ $totalRows }} rows)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="60">Row</th>
                        <th width="60">Status</th>
                        <th>Job Number</th>
                        <th>Plate Number</th>
                        <th>Customer Name</th>
                        <th>Issues</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($previewRows as $row)
                    <tr class="{{ $row['status'] === 'error' ? 'table-danger' : ($row['status'] === 'warning' ? 'table-warning' : '') }}">
                        <td>{{ $row['row'] }}</td>
                        <td>
                            @if($row['status'] === 'valid')
                                <span class="badge bg-success"><i class="bi bi-check"></i></span>
                            @elseif($row['status'] === 'warning')
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation"></i></span>
                            @else
                                <span class="badge bg-danger"><i class="bi bi-x"></i></span>
                            @endif
                        </td>
                        <td>{{ $row['job_number'] }}</td>
                        <td>{{ $row['plate_number'] }}</td>
                        <td>{{ $row['customer_name'] }}</td>
                        <td>
                            @foreach($row['errors'] as $error)
                                <span class="badge bg-danger">{{ $error }}</span>
                            @endforeach
                            @foreach($row['warnings'] as $warning)
                                <span class="badge bg-warning text-dark">{{ $warning }}</span>
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="d-flex gap-3 mt-4">
    <a href="{{ route('imports.upload') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
    
    <form action="{{ route('imports.confirm') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-primary btn-lg" 
                onclick="this.disabled=true; this.innerHTML='<i class=\'bi bi-hourglass-split me-1\'></i>Importing...'; this.form.submit();">
            <i class="bi bi-check-circle me-1"></i>
            Confirm Import ({{ $validCount + $warningCount }} rows)
        </button>
    </form>
</div>

@if($errorCount > 0)
<p class="text-muted mt-2">
    <small><i class="bi bi-info-circle me-1"></i>{{ $errorCount }} rows with errors will be skipped.</small>
</p>
@endif
@endsection
