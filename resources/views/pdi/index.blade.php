@extends('layouts.app')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-clipboard-check me-2"></i>PDI Records</h1>
        <p class="text-muted">Total: {{ $pdis->total() }} records</p>
    </div>
    <a href="{{ route('pdi-records.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add PDI Record
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center" id="searchForm">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search VIN, Plate, Model..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                <a href="{{ route('pdi-records.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                
                @auth
                <div class="dropdown">
                    <button class="btn btn-outline-dark btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-layout-three-columns"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-2" style="min-width: 200px;">
                        <h6 class="dropdown-header">Visible Columns</h6>
                        <div id="columnToggles"></div>
                        <div class="dropdown-divider"></div>
                        <button type="button" class="btn btn-primary btn-sm w-100" id="saveColumnsBtn">Save</button>
                    </div>
                </div>
                @endauth
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-sm mb-0" id="dataTable">
                <thead class="table-dark">
                    @php
                        $storedPrefs = auth()->user()?->pdi_preferences ?? [];
                        $userSort = $storedPrefs['sort'] ?? 'pdi_date';
                        $userDir = $storedPrefs['dir'] ?? 'desc';
                        $currentSort = request('sort', $userSort);
                        $currentDir = request('dir', $userDir);
                        $sortMap = [
                            'vin' => 'vin',
                            'plate' => 'plate_number',
                            'model' => 'model',
                            'colour' => 'colour',
                            'wip' => 'wip',
                            'date' => 'pdi_date',
                            'foreman' => 'technician',
                            'status' => 'status',
                        ];
                    @endphp
                    <tr id="headerRow">
                        <th data-col="no">#</th>
                        @foreach([
                            'vin' => 'VIN',
                            'plate' => 'Plate No',
                            'model' => 'Model',
                            'colour' => 'Colour',
                            'wip' => 'WIP',
                            'date' => 'PDI Date',
                            'foreman' => 'Foreman',
                            'notes' => 'Remarks',
                            'status' => 'Status',
                        ] as $col => $label)
                            @php
                                $sortable = isset($sortMap[$col]);
                                $sortField = $sortMap[$col] ?? null;
                                $isActive = $sortable && $currentSort === $sortField;
                                $nextDir = $isActive && $currentDir === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <th data-col="{{ $col }}" @if($sortable) style="cursor: pointer;" @endif>
                                @if($sortable)
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => $sortField, 'dir' => $nextDir]) }}" class="text-white text-decoration-none d-flex align-items-center justify-content-between">
                                        {{ $label }}
                                        @if($isActive)
                                            <i class="bi bi-arrow-{{ $currentDir === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="bi bi-arrow-down-up ms-1 opacity-25"></i>
                                        @endif
                                    </a>
                                @else
                                    {{ $label }}
                                @endif
                            </th>
                        @endforeach
                        <th data-col="actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($pdis as $index => $pdi)
                    <tr>
                        <td data-col="no">{{ $pdis->firstItem() + $index }}</td>
                        <td data-col="vin"><span class="fw-bold text-success">{{ $pdi->vin }}</span></td>
                        <td data-col="plate"><span class="fw-bold text-primary">{{ $pdi->plate_number }}</span></td>
                        <td data-col="model">{{ $pdi->model }}</td>
                        <td data-col="colour">{{ $pdi->colour }}</td>
                        <td data-col="wip">{{ $pdi->wip }}</td>
                        <td data-col="date">{{ $pdi->pdi_date?->format('d/m/Y') }}</td>
                        <td data-col="foreman">{{ $pdi->technician }}</td>
                        <td data-col="notes" class="text-truncate" style="max-width: 150px;">{{ $pdi->notes }}</td>
                        <td data-col="status">
                            <span class="badge bg-{{ $pdi->status === 'completed' ? 'success' : ($pdi->status === 'pending' ? 'warning' : 'info') }}">
                                {{ ucfirst(str_replace('_', ' ', $pdi->status)) }}
                            </span>
                        </td>
                        <td data-col="actions" onclick="event.stopPropagation()">
                            <a href="{{ route('pdi-records.edit', $pdi) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('pdi-records.destroy', $pdi) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No PDI records found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="d-flex align-items-center">
        <label class="me-2 small text-muted">Show</label>
        <select name="per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()" form="searchForm">
            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
            <option value="20" {{ (request('per_page') == '20' || !request('per_page')) ? 'selected' : '' }}>20</option>
            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
        </select>
        <span class="ms-2 small text-muted">entries</span>
    </div>
    {{ $pdis->withQueryString()->links() }}
</div>

@push('scripts')
@php
    $defaultPrefs = [
        'columns' => ['no' => true, 'vin' => true, 'plate' => true, 'model' => true, 'colour' => true, 'wip' => true, 'date' => true, 'foreman' => false, 'notes' => false, 'status' => true, 'actions' => true],
        'order' => ['no', 'vin', 'plate', 'model', 'colour', 'wip', 'date', 'foreman', 'notes', 'status', 'actions'],
        'widths' => [],
        'sort' => 'pdi_date',
        'dir' => 'desc'
    ];
    $storedPrefs = auth()->user()?->pdi_preferences ?? [];
    $userPrefs = array_merge($defaultPrefs['columns'], $storedPrefs['columns'] ?? []);
    $userOrder = $storedPrefs['order'] ?? $defaultPrefs['order'];
    $userWidths = $storedPrefs['widths'] ?? [];
    $userSort = $storedPrefs['sort'] ?? $defaultPrefs['sort'];
    $userDir = $storedPrefs['dir'] ?? $defaultPrefs['dir'];
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const userPrefs = @json($userPrefs);
    let userOrder = @json($userOrder);
    const userWidths = @json($userWidths);
    const userSort = @json($userSort);
    const userDir = @json($userDir);
    const columnLabels = {
        'no': '#', 'vin': 'VIN', 'plate': 'Plate No', 'model': 'Model', 'colour': 'Colour', 'wip': 'WIP', 'date': 'PDI Date',
        'foreman': 'Foreman', 'notes': 'Remarks', 'status': 'Status', 'actions': 'Actions'
    };
    const container = document.getElementById('columnToggles');
    const table = document.getElementById('dataTable');
    const headerRow = document.getElementById('headerRow');

    // Apply saved widths
    Object.keys(userWidths).forEach(col => {
        const th = table.querySelector(`th[data-col="${col}"]`);
        if(th) th.style.width = userWidths[col];
    });

    // Apply saved column order
    function applyColumnOrder(order) {
        order.forEach((col) => {
            const th = headerRow.querySelector(`th[data-col="${col}"]`);
            if (th) headerRow.appendChild(th);
        });
        document.querySelectorAll('#tableBody tr').forEach(row => {
            order.forEach(col => {
                const td = row.querySelector(`td[data-col="${col}"]`);
                if (td) row.appendChild(td);
            });
        });
    }
    applyColumnOrder(userOrder);

    // Build column toggles with drag handles
    function buildToggles() {
        container.innerHTML = '';
        userOrder.forEach(key => {
            if (!columnLabels[key]) return;
            const div = document.createElement('div');
            div.className = 'form-check d-flex align-items-center py-1';
            div.draggable = true;
            div.dataset.col = key;
            div.innerHTML = `
                <i class="bi bi-grip-vertical text-muted me-2" style="cursor: grab;"></i>
                <input class="form-check-input col-toggle" type="checkbox" value="${key}" id="col_${key}" ${userPrefs[key] ? 'checked' : ''}>
                <label class="form-check-label ms-1 small" for="col_${key}">${columnLabels[key]}</label>
            `;
            container.appendChild(div);
        });
        setupDragDrop();
    }
    buildToggles();

    function setupDragDrop() {
        let draggedEl = null;
        container.querySelectorAll('[draggable]').forEach(el => {
            el.addEventListener('dragstart', e => { draggedEl = el; el.classList.add('opacity-50'); e.dataTransfer.effectAllowed = 'move'; });
            el.addEventListener('dragend', e => { el.classList.remove('opacity-50'); container.querySelectorAll('.drag-over').forEach(x => x.classList.remove('drag-over', 'border-top', 'border-primary')); draggedEl = null; });
            el.addEventListener('dragover', e => { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; el.classList.add('drag-over', 'border-top', 'border-primary'); });
            el.addEventListener('dragleave', e => { el.classList.remove('drag-over', 'border-top', 'border-primary'); });
            el.addEventListener('drop', e => { e.preventDefault(); el.classList.remove('drag-over', 'border-top', 'border-primary'); if (draggedEl && draggedEl !== el) { container.insertBefore(draggedEl, el); updateOrderFromDOM(); applyColumnOrderFromDOM(); } });
        });
    }

    function updateOrderFromDOM() { userOrder = []; container.querySelectorAll('[data-col]').forEach(el => userOrder.push(el.dataset.col)); }
    function applyColumnOrderFromDOM() { const order = []; container.querySelectorAll('[data-col]').forEach(el => order.push(el.dataset.col)); applyColumnOrder(order); }

    function applyVisibility() {
        document.querySelectorAll('.col-toggle').forEach(toggle => {
            const colName = toggle.value;
            const visible = toggle.checked;
            const th = table.querySelector(`th[data-col="${colName}"]`);
            if(th) th.style.display = visible ? '' : 'none';
            table.querySelectorAll(`td[data-col="${colName}"]`).forEach(td => td.style.display = visible ? '' : 'none');
        });
    }
    applyVisibility();
    container.addEventListener('change', applyVisibility);

    const urlParams = new URLSearchParams(window.location.search);
    const currentSort = urlParams.get('sort') || userSort;
    const currentDir = urlParams.get('dir') || userDir;

    document.getElementById('saveColumnsBtn').addEventListener('click', function() {
        const prefs = {};
        document.querySelectorAll('.col-toggle').forEach(t => prefs[t.value] = t.checked);
        const widths = {};
        table.querySelectorAll('th').forEach(th => { if(th.dataset.col && th.style.width) widths[th.dataset.col] = th.style.width; });
        const order = [];
        container.querySelectorAll('[data-col]').forEach(el => order.push(el.dataset.col));

        fetch('{{ route("preferences.columns") }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({ columns: prefs, widths: widths, order: order, sort: currentSort, dir: currentDir, table: 'pdi' })
        }).then(res => res.json()).then(data => {
            if(data.success) {
                const btn = document.getElementById('saveColumnsBtn');
                btn.innerHTML = '<i class="bi bi-check"></i> Saved!';
                btn.classList.replace('btn-primary', 'btn-success');
                setTimeout(() => { btn.innerHTML = 'Save'; btn.classList.replace('btn-success', 'btn-primary'); }, 1500);
            }
        }).catch(err => alert('Error: ' + err.message));
    });

    // Column Resizing
    table.querySelectorAll('th').forEach(th => {
        const resizer = document.createElement('div');
        resizer.style.cssText = 'width:5px;height:100%;position:absolute;right:0;top:0;cursor:col-resize;user-select:none;z-index:10;';
        th.appendChild(resizer);
        th.style.position = 'relative';
        let startX, startWidth;
        resizer.addEventListener('mousedown', e => { e.stopPropagation(); startX = e.pageX; startWidth = th.offsetWidth; document.addEventListener('mousemove', onMove); document.addEventListener('mouseup', onUp); });
        function onMove(e) { th.style.width = (startWidth + e.pageX - startX) + 'px'; }
        function onUp() { document.removeEventListener('mousemove', onMove); document.removeEventListener('mouseup', onUp); }
    });
});
</script>
@endpush
@endsection
