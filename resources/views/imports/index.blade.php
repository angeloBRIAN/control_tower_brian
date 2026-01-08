@extends('layouts.app')

@section('title', 'Import History')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-cloud-upload me-2"></i>Import History</h1>
        <p class="text-muted">View past data imports</p>
    </div>
    <a href="{{ route('imports.upload') }}" class="btn btn-primary">
        <i class="bi bi-file-earmark-arrow-up me-2"></i>Upload File
    </a>
</div>



<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>File Name</th>
                    <th>Type</th>
                    <th class="text-center">Imported</th>
                    <th class="text-center">Updated</th>
                    <th class="text-center">Failed</th>
                    <th>By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($imports as $import)
                <tr>
                    <td>{{ $import->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $import->file_name }}</td>
                    <td><span class="badge bg-{{ $import->type_color }}">{{ $import->type_label }}</span></td>
                    <td class="text-center"><span class="badge bg-success">{{ $import->records_imported }}</span></td>
                    <td class="text-center"><span class="badge bg-primary">{{ $import->records_updated }}</span></td>
                    <td class="text-center">
                        @if($import->records_failed > 0)
                            <span class="badge bg-danger">{{ $import->records_failed }}</span>
                        @else
                            <span class="badge bg-secondary">0</span>
                        @endif
                    </td>
                    <td>{{ $import->user->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('imports.show', $import) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Details
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No imports yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $imports->links() }}
</div>
@endsection
