@extends('layouts.app')

@section('title', 'Edit Announcement')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
.ql-editor { min-height: 200px; }
</style>
@endpush

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.announcements.index') }}">Announcements</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
    <h2><i class="bi bi-pencil me-2"></i>Edit Announcement</h2>
</div>

<form action="{{ route('admin.announcements.update', $announcement) }}" method="POST" id="announcementForm">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-light">Content</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $announcement->title) }}" required>
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <div id="editor">{!! old('content', $announcement->content) !!}</div>
                        <input type="hidden" name="content" id="content">
                        @error('content')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="card mb-4 bg-light">
                <div class="card-body">
                    <small class="text-muted">
                        <strong>Created:</strong> {{ $announcement->created_at->format('d M Y H:i') }}
                        by {{ $announcement->author->name ?? 'Unknown' }}
                        @if($announcement->dismissed_by)
                        • <strong>Dismissed by:</strong> {{ count($announcement->dismissed_by) }} users
                        @endif
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-light">Options</div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_important" 
                                   name="is_important" value="1" {{ $announcement->is_important ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_important">
                                <i class="bi bi-exclamation-triangle me-1 text-danger"></i>Mark as Important
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_pinned" 
                                   name="is_pinned" value="1" {{ $announcement->is_pinned ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_pinned">
                                <i class="bi bi-pin-fill me-1 text-primary"></i>Pin to Top
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-light">Target Audience</div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="target_type" 
                                   id="target_all" value="all" 
                                   {{ empty($announcement->target_roles) ? 'checked' : '' }} onchange="toggleRoles()">
                            <label class="form-check-label" for="target_all">All Users</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="target_type" 
                                   id="target_specific" value="specific"
                                   {{ !empty($announcement->target_roles) ? 'checked' : '' }} onchange="toggleRoles()">
                            <label class="form-check-label" for="target_specific">Specific Roles</label>
                        </div>
                    </div>
                    
                    <div id="rolesContainer" style="{{ empty($announcement->target_roles) ? 'display: none;' : '' }}">
                        @foreach($roles as $role)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="target_roles[]" value="{{ $role }}" id="role_{{ $role }}"
                                   {{ in_array($role, $announcement->target_roles ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_{{ $role }}">
                                {{ ucfirst(str_replace('_', ' ', $role)) }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-light">Scheduling</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="expires_at" class="form-label">Expiry Date</label>
                        <input type="datetime-local" class="form-control" id="expires_at" name="expires_at"
                               value="{{ $announcement->expires_at?->format('Y-m-d\TH:i') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-lg px-5">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
        <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link'],
                ['clean']
            ]
        }
    });
    
    document.getElementById('announcementForm').addEventListener('submit', function() {
        document.getElementById('content').value = quill.root.innerHTML;
    });
});

function toggleRoles() {
    const container = document.getElementById('rolesContainer');
    const isSpecific = document.getElementById('target_specific').checked;
    container.style.display = isSpecific ? 'block' : 'none';
}
</script>
@endpush
