@extends('layouts.app')

@section('title', 'Help Center')

@section('content')
<div class="page-header mb-4">
    <h1><i class="bi bi-question-circle me-2"></i>Help Center</h1>
    <p class="text-muted mb-0">Documentation and guides for Control Tower</p>
</div>

<!-- Quick Start Section -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Start</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Daily Operations</h6>
                        <ol class="mb-4">
                            <li>Export data from DMS to Excel</li>
                            <li>Import via <strong>Operations → Import</strong></li>
                            <li>Review Dashboard for job counts</li>
                            <li>Use Kanban board for workflow</li>
                            <li>Mark invoiced jobs at end of day</li>
                        </ol>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Common Tasks</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-search text-primary me-2"></i>Press <kbd>Ctrl</kbd>+<kbd>K</kbd> to search</li>
                            <li><i class="bi bi-plus-circle text-success me-2"></i>Press <kbd>N</kbd> to create new job</li>
                            <li><i class="bi bi-printer text-secondary me-2"></i>Use Print button on job details</li>
                            <li><i class="bi bi-clock-history text-info me-2"></i>Check Recently Viewed in sidebar</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Keyboard Shortcuts -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-keyboard me-2"></i>Keyboard Shortcuts</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach($shortcuts as $shortcut)
                        <tr>
                            <td><kbd>{{ $shortcut['key'] }}</kbd></td>
                            <td>{{ $shortcut['action'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted small">
                Press <kbd>?</kbd> anywhere to see all shortcuts
            </div>
        </div>
    </div>
</div>

<!-- Documentation Cards -->
<h4 class="mb-3"><i class="bi bi-book me-2"></i>Documentation</h4>
<div class="row g-4">
    @foreach($documents as $slug => $doc)
    <div class="col-md-6 col-lg-4">
        <a href="{{ route('help.show', $slug) }}" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                            <i class="{{ $doc['icon'] }} fs-4 text-primary"></i>
                        </div>
                        <h5 class="mb-0 text-dark">{{ $doc['title'] }}</h5>
                    </div>
                    <p class="text-muted mb-0">{{ $doc['description'] }}</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <span class="text-primary">Read documentation <i class="bi bi-arrow-right"></i></span>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

<!-- Support Section -->
<div class="card border-0 shadow-sm mt-4">
    <div class="card-body text-center py-4">
        <h5>Need More Help?</h5>
        <p class="text-muted mb-3">Contact your system administrator or the Control Tower support team.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="mailto:support@example.com" class="btn btn-outline-primary">
                <i class="bi bi-envelope me-1"></i>Email Support
            </a>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#keyboardShortcutsModal">
                <i class="bi bi-keyboard me-1"></i>View All Shortcuts
            </button>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}
kbd {
    background: var(--bs-gray-800);
    padding: 0.15rem 0.4rem;
    border-radius: 3px;
    font-size: 0.85em;
}
</style>
@endsection
