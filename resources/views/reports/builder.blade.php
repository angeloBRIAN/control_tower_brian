@extends('layouts.app')

@section('title', 'Report Builder')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-file-earmark-bar-graph me-2"></i>Report Builder</h1>
        <p class="text-muted mb-0">Create custom reports with selected columns and filters</p>
    </div>
</div>

<div class="row g-4">
    <!-- Left Panel: Configuration -->
    <div class="col-lg-4">
        <!-- Saved Reports -->
        <div class="card mb-3">
            <div class="card-header py-2">
                <i class="bi bi-bookmark me-2"></i>Saved Reports
            </div>
            <div class="card-body py-2">
                @if($savedReports->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($savedReports as $report)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-1">
                            <a href="#" class="text-decoration-none load-report" data-id="{{ $report->id }}">
                                <i class="bi bi-file-text me-1"></i>{{ $report->name }}
                            </a>
                            <button class="btn btn-sm btn-outline-danger delete-report" data-id="{{ $report->id }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0 small">No saved reports yet</p>
                @endif
            </div>
        </div>

        <!-- Column Selection -->
        <div class="card mb-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-layout-three-columns me-2"></i>Columns</span>
                <div>
                    <button type="button" class="btn btn-sm btn-link p-0 me-2" id="selectAllCols">All</button>
                    <button type="button" class="btn btn-sm btn-link p-0" id="selectNoneCols">None</button>
                </div>
            </div>
            <div class="card-body py-2" style="max-height: 400px; overflow-y: auto;">
                @foreach($groupedColumns as $group => $cols)
                <div class="mb-2">
                    <small class="text-muted fw-bold">{{ $group }}</small>
                    <div class="row g-1">
                        @foreach($cols as $key => $col)
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input column-checkbox" type="checkbox" value="{{ $key }}" id="col_{{ $key }}" 
                                    {{ in_array($key, ['job_number', 'customer_name', 'service_advisor', 'status', 'total_sales']) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="col_{{ $key }}">{{ $col['label'] }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-header py-2">
                <i class="bi bi-funnel me-2"></i>Filters
            </div>
            <div class="card-body py-2">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small mb-0">Date From</label>
                        <input type="date" class="form-control form-control-sm filter-input" id="filter_date_from">
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-0">Date To</label>
                        <input type="date" class="form-control form-control-sm filter-input" id="filter_date_to">
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-0">Franchise</label>
                        <select class="form-select form-select-sm filter-input" id="filter_franchise">
                            <option value="">All</option>
                            @foreach($filterOptions['franchise'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-0">Status</label>
                        <select class="form-select form-select-sm filter-input" id="filter_status">
                            <option value="">All</option>
                            @foreach($filterOptions['status'] as $opt)
                            <option value="{{ $opt }}">{{ ucfirst($opt) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-0">SA</label>
                        <select class="form-select form-select-sm filter-input" id="filter_service_advisor">
                            <option value="">All</option>
                            @foreach($filterOptions['service_advisor'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-0">Foreman</label>
                        <select class="form-select form-select-sm filter-input" id="filter_foreman">
                            <option value="">All</option>
                            @foreach($filterOptions['foreman'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-0">Department</label>
                        <select class="form-select form-select-sm filter-input" id="filter_department">
                            <option value="">All</option>
                            @foreach($filterOptions['department'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-0">Work Status</label>
                        <select class="form-select form-select-sm filter-input" id="filter_work_status">
                            <option value="">All</option>
                            @foreach($filterOptions['work_status'] as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- PDF/Print Settings -->
        <div class="card mb-3">
            <div class="card-header py-2" data-bs-toggle="collapse" data-bs-target="#pdfSettings" role="button">
                <i class="bi bi-file-pdf me-2"></i>PDF/Print Settings
                <i class="bi bi-chevron-down float-end"></i>
            </div>
            <div class="collapse show" id="pdfSettings">
                <div class="card-body py-2">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label small mb-0">Report Title</label>
                            <input type="text" class="form-control form-control-sm" id="reportTitle" placeholder="Job Report">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-0">Title Align</label>
                            <select class="form-select form-select-sm" id="titleAlign">
                                <option value="center">Center</option>
                                <option value="left">Left</option>
                                <option value="right">Right</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-0">Orientation</label>
                            <select class="form-select form-select-sm" id="pageOrientation">
                                <option value="portrait">Portrait</option>
                                <option value="landscape">Landscape</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small mb-0">Header Text</label>
                            <input type="text" class="form-control form-control-sm" id="headerText" placeholder="e.g. {title} - Generated {date}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small mb-0">Footer Text</label>
                            <input type="text" class="form-control form-control-sm" id="footerText" placeholder="e.g. Page {page} of {pages}" value="Page {page} of {pages}">
                        </div>
                        <div class="col-12">
                            <small class="text-muted">
                                <strong>Variables:</strong> <code>{page}</code> <code>{pages}</code> <code>{date}</code> <code>{time}</code> <code>{title}</code>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-body py-3">
                <button type="button" class="btn btn-primary w-100 mb-2" id="previewBtn">
                    <i class="bi bi-eye me-1"></i>Preview
                </button>
                <div class="btn-group w-100 mb-2">
                    <button type="button" class="btn btn-success" id="exportExcel">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel
                    </button>
                    <button type="button" class="btn btn-success" id="exportCsv">
                        <i class="bi bi-filetype-csv me-1"></i>CSV
                    </button>
                    <button type="button" class="btn btn-danger" id="exportPdf">
                        <i class="bi bi-file-pdf me-1"></i>PDF
                    </button>
                </div>
                <button type="button" class="btn btn-outline-secondary w-100" data-bs-toggle="modal" data-bs-target="#saveModal">
                    <i class="bi bi-save me-1"></i>Save Report Config
                </button>
            </div>
        </div>
    </div>

    <!-- Right Panel: Preview -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-table me-2"></i>Preview</span>
                <span class="badge bg-secondary" id="totalCount">0 records</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 70vh; overflow: auto;">
                    <table class="table table-hover table-sm table-bordered mb-0" id="previewTable">
                        <thead class="table-dark" style="position: sticky; top: 0;">
                            <tr id="previewHeader"></tr>
                        </thead>
                        <tbody id="previewBody">
                            <tr>
                                <td class="text-center text-muted py-5">
                                    <i class="bi bi-arrow-left fs-1 opacity-25"></i>
                                    <p class="mb-0 mt-2">Select columns and click Preview</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Save Modal -->
<div class="modal fade" id="saveModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-save me-2"></i>Save Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Report Name</label>
                <input type="text" class="form-control" id="reportName" placeholder="My Custom Report">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveReportBtn">Save</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '{{ csrf_token() }}';
    
    // Get selected columns
    function getSelectedColumns() {
        return Array.from(document.querySelectorAll('.column-checkbox:checked')).map(cb => cb.value);
    }
    
    // Get filters
    function getFilters() {
        return {
            date_from: document.getElementById('filter_date_from').value,
            date_to: document.getElementById('filter_date_to').value,
            franchise: document.getElementById('filter_franchise').value,
            status: document.getElementById('filter_status').value,
            service_advisor: document.getElementById('filter_service_advisor').value,
            foreman: document.getElementById('filter_foreman').value,
            department: document.getElementById('filter_department').value,
            work_status: document.getElementById('filter_work_status').value,
        };
    }
    
    // Build query string
    function buildQueryString() {
        const columns = getSelectedColumns();
        const filters = getFilters();
        const params = new URLSearchParams();
        columns.forEach(c => params.append('columns[]', c));
        Object.entries(filters).forEach(([k, v]) => { if (v) params.append(k, v); });
        return params.toString();
    }
    
    // Preview
    document.getElementById('previewBtn').addEventListener('click', async function() {
        const columns = getSelectedColumns();
        if (columns.length === 0) {
            alert('Please select at least one column');
            return;
        }
        
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
        
        try {
            const response = await fetch('{{ route("reports.preview") }}?' + buildQueryString());
            const data = await response.json();
            
            if (data.success) {
                // Update header
                const header = document.getElementById('previewHeader');
                header.innerHTML = Object.values(data.columns).map(c => `<th>${c.label}</th>`).join('');
                
                // Update body
                const body = document.getElementById('previewBody');
                if (data.data.length > 0) {
                    body.innerHTML = data.data.map(row => 
                        '<tr>' + Object.values(row).map(v => `<td>${v}</td>`).join('') + '</tr>'
                    ).join('');
                } else {
                    body.innerHTML = '<tr><td colspan="100" class="text-center text-muted py-3">No data found</td></tr>';
                }
                
                document.getElementById('totalCount').textContent = data.total + ' records';
            }
        } catch (error) {
            console.error(error);
            alert('Error loading preview');
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-eye me-1"></i>Preview';
        }
    });
    
    // Get PDF settings
    function getPdfSettings() {
        return {
            title: document.getElementById('reportTitle').value || 'Job Report',
            title_align: document.getElementById('titleAlign').value,
            orientation: document.getElementById('pageOrientation').value,
            header: document.getElementById('headerText').value,
            footer: document.getElementById('footerText').value,
        };
    }
    
    // Build query string with PDF settings
    function buildExportQueryString(format) {
        const columns = getSelectedColumns();
        const filters = getFilters();
        const pdfSettings = getPdfSettings();
        const params = new URLSearchParams();
        
        columns.forEach(c => params.append('columns[]', c));
        Object.entries(filters).forEach(([k, v]) => { if (v) params.append(k, v); });
        Object.entries(pdfSettings).forEach(([k, v]) => { if (v) params.append(k, v); });
        params.append('format', format);
        
        return params.toString();
    }
    
    // Export handlers
    document.getElementById('exportExcel').addEventListener('click', () => {
        if (getSelectedColumns().length === 0) { alert('Select columns first'); return; }
        window.location.href = '{{ route("reports.export") }}?' + buildExportQueryString('xlsx');
    });
    
    document.getElementById('exportCsv').addEventListener('click', () => {
        if (getSelectedColumns().length === 0) { alert('Select columns first'); return; }
        window.location.href = '{{ route("reports.export") }}?' + buildExportQueryString('csv');
    });
    
    document.getElementById('exportPdf').addEventListener('click', () => {
        if (getSelectedColumns().length === 0) { alert('Select columns first'); return; }
        window.open('{{ route("reports.export") }}?' + buildExportQueryString('pdf'), '_blank');
    });
    
    // Select all/none
    document.getElementById('selectAllCols').addEventListener('click', () => {
        document.querySelectorAll('.column-checkbox').forEach(cb => cb.checked = true);
    });
    document.getElementById('selectNoneCols').addEventListener('click', () => {
        document.querySelectorAll('.column-checkbox').forEach(cb => cb.checked = false);
    });
    
    // Save report
    document.getElementById('saveReportBtn').addEventListener('click', async function() {
        const name = document.getElementById('reportName').value.trim();
        if (!name) { alert('Please enter a report name'); return; }
        
        const columns = getSelectedColumns();
        if (columns.length === 0) { alert('Select at least one column'); return; }
        
        try {
            const response = await fetch('{{ route("reports.save") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ name, columns, filters: getFilters() })
            });
            const data = await response.json();
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('saveModal')).hide();
                location.reload();
            }
        } catch (error) {
            alert('Error saving report');
        }
    });
    
    // Load report
    document.querySelectorAll('.load-report').forEach(link => {
        link.addEventListener('click', async function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            try {
                const response = await fetch(`/reports/${id}/load`);
                const data = await response.json();
                if (data.success) {
                    // Uncheck all, then check saved columns
                    document.querySelectorAll('.column-checkbox').forEach(cb => cb.checked = false);
                    data.report.columns.forEach(col => {
                        const cb = document.getElementById('col_' + col);
                        if (cb) cb.checked = true;
                    });
                    // Set filters
                    if (data.report.filters) {
                        Object.entries(data.report.filters).forEach(([k, v]) => {
                            const el = document.getElementById('filter_' + k);
                            if (el) el.value = v || '';
                        });
                    }
                    // Auto-preview
                    document.getElementById('previewBtn').click();
                }
            } catch (error) {
                alert('Error loading report');
            }
        });
    });
    
    // Delete report
    document.querySelectorAll('.delete-report').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            if (!confirm('Delete this saved report?')) return;
            try {
                await fetch(`/reports/${this.dataset.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                location.reload();
            } catch (error) {
                alert('Error deleting report');
            }
        });
    });
});
</script>
@endpush
@endsection
