@extends('layouts.app')

@section('title', 'Create Announcement')

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
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
    <h2><i class="bi bi-megaphone-fill me-2"></i>New Announcement</h2>
</div>

<form action="{{ route('admin.announcements.store') }}" method="POST" id="announcementForm">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-light">Content</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" 
                               placeholder="Announcement title..." required>
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <div id="editor">{!! old('content') !!}</div>
                        <input type="hidden" name="content" id="content">
                        @error('content')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
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
                                   name="is_important" value="1" {{ old('is_important') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_important">
                                <i class="bi bi-exclamation-triangle me-1 text-danger"></i>Mark as Important
                            </label>
                        </div>
                        <small class="text-muted">Highlighted with red styling</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_pinned" 
                                   name="is_pinned" value="1" {{ old('is_pinned') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_pinned">
                                <i class="bi bi-pin-fill me-1 text-primary"></i>Pin to Top
                            </label>
                        </div>
                        <small class="text-muted">Always show at top of list</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="send_push" 
                                   name="send_push" value="1" {{ old('send_push', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="send_push">
                                <i class="bi bi-bell-fill me-1 text-warning"></i>Send Push Notification
                            </label>
                        </div>
                        <small class="text-muted">Notify users immediately</small>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-light">Target Audience</div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="target_type" 
                                   id="target_all" value="all" checked onchange="toggleRoles()">
                            <label class="form-check-label" for="target_all">
                                All Users
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="target_type" 
                                   id="target_specific" value="specific" onchange="toggleRoles()">
                            <label class="form-check-label" for="target_specific">
                                Specific Roles
                            </label>
                        </div>
                    </div>
                    
                    <div id="rolesContainer" style="display: none;">
                        @foreach($roles as $role)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="target_roles[]" value="{{ $role }}" id="role_{{ $role }}">
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
                        <label for="published_at" class="form-label">Publish Date (Optional)</label>
                        <input type="datetime-local" class="form-control" id="published_at" name="published_at"
                               value="{{ old('published_at') }}">
                        <small class="text-muted">Leave empty to publish immediately</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expires_at" class="form-label">Expiry Date (Optional)</label>
                        <input type="datetime-local" class="form-control" id="expires_at" name="expires_at"
                               value="{{ old('expires_at') }}">
                        <small class="text-muted">Leave empty for no expiry</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-lg px-5">
            <i class="bi bi-send me-2"></i>Publish Announcement
        </button>
        <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill editor
    const quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Write your announcement message here...',
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
    
    // Update hidden input before submit
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
