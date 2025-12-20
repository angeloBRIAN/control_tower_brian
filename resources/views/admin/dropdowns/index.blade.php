@extends('layouts.app')

@section('title', 'Dropdown Options')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-list-ul me-2"></i>Dropdown Options</h1>
        <p class="text-muted mb-0">Manage configurable dropdown values for jobs</p>
    </div>
    <a href="{{ route('admin.dropdowns.create', ['type' => $currentType]) }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Option
    </a>
</div>

<!-- Type Tabs -->
<ul class="nav nav-tabs mb-4">
    @foreach($types as $typeKey => $typeLabel)
    <li class="nav-item">
        <a class="nav-link {{ $currentType === $typeKey ? 'active' : '' }}" 
           href="{{ route('admin.dropdowns.index', ['type' => $typeKey]) }}">
            {{ $typeLabel }}
            <span class="badge bg-secondary ms-1">
                {{ \App\Models\DropdownOption::where('type', $typeKey)->count() }}
            </span>
        </a>
    </li>
    @endforeach
</ul>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Label</th>
                    <th>Value</th>
                    <th class="text-center">Icon</th>
                    <th class="text-center">Color</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="sortableOptions">
                @forelse($options as $option)
                <tr data-id="{{ $option->id }}">
                    <td class="text-muted">{{ $option->sort_order }}</td>
                    <td class="fw-bold">{{ $option->label }}</td>
                    <td><code>{{ $option->value }}</code></td>
                    <td class="text-center">
                        @if($option->icon)
                            <i class="bi bi-{{ $option->icon }}"></i>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $option->color }}">{{ $option->color }}</span>
                    </td>
                    <td class="text-center">
                        @if($option->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.dropdowns.edit', $option) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.dropdowns.destroy', $option) }}" method="POST" class="d-inline" 
                              onsubmit="return confirm('Delete this option?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox display-4 d-block mb-2 opacity-25"></i>
                        No options defined for {{ $types[$currentType] }}
                        <br>
                        <a href="{{ route('admin.dropdowns.create', ['type' => $currentType]) }}" class="btn btn-primary btn-sm mt-2">
                            <i class="bi bi-plus-lg me-1"></i>Add First Option
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="alert alert-info mt-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Tip:</strong> These options will appear in dropdown menus when editing jobs. 
    The <strong>Value</strong> is stored in the database, while <strong>Label</strong> is shown to users.
</div>
@endsection
