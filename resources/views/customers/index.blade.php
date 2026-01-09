@extends('layouts.app')

@section('title', 'Customer Lookup')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-people me-2"></i>Customer Lookup</h1>
        <p class="text-muted mb-0">{{ number_format($totalCustomers) }} customers @if($dmsLinkedCount > 0)<span class="badge bg-info">{{ $dmsLinkedCount }} DMS linked</span>@endif</p>
    </div>
    <a href="{{ route('customers.duplicates') }}" class="btn btn-warning">
        <i class="bi bi-arrow-left-right me-1"></i>Merge Duplicates
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center" id="searchForm">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search customer name..." value="{{ $search }}" autofocus>
            </div>
            <div class="col-md-2">
                <select name="filter" class="form-select form-select-sm">
                    <option value="">All Customers</option>
                    <option value="with_uninvoiced" {{ request('filter') == 'with_uninvoiced' ? 'selected' : '' }}>With Uninvoiced</option>
                    <option value="with_sales" {{ request('filter') == 'with_sales' ? 'selected' : '' }}>With Sales</option>
                    <option value="multi_vehicle" {{ request('filter') == 'multi_vehicle' ? 'selected' : '' }}>Multi Vehicles</option>
                    <option value="dms_linked" {{ request('filter') == 'dms_linked' ? 'selected' : '' }}>DMS Linked</option>
                    <option value="not_linked" {{ request('filter') == 'not_linked' ? 'selected' : '' }}>Not Linked</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>

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
                        $storedPrefs = auth()->user()?->customer_preferences ?? [];
                        $userSort = $storedPrefs['sort'] ?? 'name';
                        $userDir = $storedPrefs['dir'] ?? 'asc';
                        $currentSort = request('sort', $sortField ?? $userSort);
                        $currentDir = request('dir', $sortDir ?? $userDir);
                        $sortMap = [
                            'name' => 'name',
                            'vehicles' => 'vehicle_count',
                            'jobs' => 'job_count',
                            'uninvoiced' => 'uninvoiced_count',
                            'sales' => 'sales_amount',
                        ];
                    @endphp
                    <tr id="headerRow">
                        <th data-col="no">#</th>
                        @foreach([
                            'name' => 'Customer Name',
                            'dms' => 'DMS',
                            'contact' => 'Contact',
                            'vehicles' => 'Vehicles',
                            'jobs' => 'Total Jobs',
                            'uninvoiced' => 'Uninvoiced',
                            'sales' => 'Sales Amount',
                        ] as $col => $label)
                            @php
                                $sortable = isset($sortMap[$col]);
                                $sortField = $sortMap[$col] ?? null;
                                $isActive = $sortable && $currentSort === $sortField;
                                $nextDir = $isActive && $currentDir === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <th data-col="{{ $col }}" @if($sortable) style="cursor: pointer;" @endif class="{{ in_array($col, ['vehicles', 'jobs', 'uninvoiced']) ? 'text-center' : ($col === 'sales' ? 'text-end' : '') }}">
                                @if($sortable)
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => $sortField, 'dir' => $nextDir]) }}" class="text-white text-decoration-none d-flex align-items-center {{ in_array($col, ['vehicles', 'jobs', 'uninvoiced']) ? 'justify-content-center' : ($col === 'sales' ? 'justify-content-end' : 'justify-content-between') }}">
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
                    @forelse($customerData as $index => $customer)
                    <tr>
                        <td data-col="no">{{ $customers->firstItem() + $index }}</td>
                        <td data-col="name">
                            <a href="{{ route('customers.show', ['name' => $customer->name]) }}" class="fw-bold text-primary">
                                {{ $customer->name }}
                            </a>
                            @if($customer->is_dms_linked)
                            <span class="badge bg-info ms-1" title="DMS Linked"><i class="bi bi-link-45deg"></i></span>
                            @endif
                            @if($customer->company_name)
                            <div class="small text-muted">{{ Str::limit($customer->company_name, 30) }}</div>
                            @endif
                        </td>
                        <td data-col="dms">
                            @if($customer->dms_magic)
                            <span class="badge bg-secondary">{{ $customer->dms_magic }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td data-col="contact">
                            @if($customer->email)
                            <div class="small text-truncate" style="max-width: 150px;" title="{{ $customer->email }}"><i class="bi bi-envelope me-1"></i>{{ $customer->email }}</div>
                            @endif
                            @if($customer->phone)
                            <div class="small"><i class="bi bi-phone me-1"></i>{{ $customer->phone }}</div>
                            @endif
                            @if(!$customer->email && !$customer->phone)
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td data-col="vehicles" class="text-center">
                            <span class="badge bg-info">{{ $customer->vehicle_count }}</span>
                        </td>
                        <td data-col="jobs" class="text-center">
                            <span class="badge bg-primary">{{ $customer->job_count }}</span>
                        </td>
                        <td data-col="uninvoiced" class="text-center">
                            @if($customer->uninvoiced_count > 0)
                                <span class="badge bg-warning text-dark">{{ $customer->uninvoiced_count }}</span>
                            @else
                                <span class="badge bg-success">0</span>
                            @endif
                        </td>
                        <td data-col="sales" class="text-end">
                            @if($customer->sales_amount > 0)
                                <span class="text-success fw-bold">{{ number_format($customer->sales_amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td data-col="actions">
                            <a href="{{ route('customers.show', ['name' => $customer->name]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            @if($search)
                                No customers found matching "{{ $search }}"
                            @else
                                No customers found
                            @endif
                        </td>
                    </tr>
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
            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
        </select>
        <span class="ms-2 small text-muted">entries</span>
    </div>
    {{ $customers->links() }}
</div>

@push('scripts')
@php
    $defaultPrefs = [
        'columns' => ['no' => true, 'name' => true, 'dms' => true, 'contact' => true, 'vehicles' => true, 'jobs' => true, 'uninvoiced' => true, 'sales' => true, 'actions' => true],
        'order' => ['no', 'name', 'dms', 'contact', 'vehicles', 'jobs', 'uninvoiced', 'sales', 'actions'],
        'widths' => [],
        'sort' => 'name',
        'dir' => 'asc'
    ];
    $storedPrefs = auth()->user()?->customer_preferences ?? [];
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
        'no': '#', 'name': 'Customer Name', 'dms': 'DMS', 'contact': 'Contact', 'vehicles': 'Vehicles', 'jobs': 'Total Jobs',
        'uninvoiced': 'Uninvoiced', 'sales': 'Sales Amount', 'actions': 'Actions'
    };
    const container = document.getElementById('columnToggles');
    const table = document.getElementById('dataTable');
    const headerRow = document.getElementById('headerRow');

    if (!container) return;

    Object.keys(userWidths).forEach(col => {
        const th = table.querySelector(`th[data-col="${col}"]`);
        if(th) th.style.width = userWidths[col];
    });

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
            body: JSON.stringify({ columns: prefs, widths: widths, order: order, sort: currentSort, dir: currentDir, table: 'customer' })
        }).then(res => res.json()).then(data => {
            if(data.success) {
                const btn = document.getElementById('saveColumnsBtn');
                btn.innerHTML = '<i class="bi bi-check"></i> Saved!';
                btn.classList.replace('btn-primary', 'btn-success');
                setTimeout(() => { btn.innerHTML = 'Save'; btn.classList.replace('btn-success', 'btn-primary'); }, 1500);
            }
        }).catch(err => alert('Error: ' + err.message));
    });

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
