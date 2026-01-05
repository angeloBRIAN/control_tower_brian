@extends('layouts.app')

@section('title', $title . ' - Help')

@section('content')
<div class="row">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3 mb-4">
        <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header">
                <a href="{{ route('help.index') }}" class="text-decoration-none text-dark">
                    <i class="bi bi-arrow-left me-2"></i>Help Center
                </a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($documents as $slug => $doc)
                    <a href="{{ route('help.show', $slug) }}" 
                       class="list-group-item list-group-item-action d-flex align-items-center {{ $currentSlug === $slug ? 'active' : '' }}">
                        <i class="{{ $doc['icon'] }} me-2"></i>
                        {{ $doc['title'] }}
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Table of Contents -->
        @if(count($toc) > 0)
        <div class="card border-0 shadow-sm mt-3 d-none d-lg-block">
            <div class="card-header">
                <small class="fw-bold text-muted">ON THIS PAGE</small>
            </div>
            <div class="card-body p-0">
                <nav class="toc-nav">
                    <ul class="list-unstyled mb-0">
                        @foreach($toc as $item)
                        <li class="{{ $item['level'] === 3 ? 'ps-3' : '' }}">
                            <a href="#{{ $item['slug'] }}" class="d-block py-1 px-3 text-decoration-none text-muted small {{ $item['level'] === 2 ? 'fw-semibold' : '' }}">
                                {{ \Str::limit($item['title'], 35) }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </nav>
            </div>
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <i class="{{ $icon }} me-2 text-primary"></i>
                    <span class="fw-bold">{{ $title }}</span>
                </div>
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Print
                </button>
            </div>
            <div class="card-body doc-content">
                {!! $content !!}
            </div>
        </div>
    </div>
</div>

<style>
/* Documentation Content Styling */
.doc-content {
    font-size: 0.95rem;
    line-height: 1.7;
}

.doc-content h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--bs-primary);
}

.doc-content h2 {
    font-size: 1.4rem;
    font-weight: 600;
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: var(--bs-primary);
}

.doc-content h3 {
    font-size: 1.15rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.doc-content h4 {
    font-size: 1rem;
    font-weight: 600;
    margin-top: 1.25rem;
}

.doc-content table {
    width: 100%;
    margin: 1rem 0;
    border-collapse: collapse;
}

.doc-content th,
.doc-content td {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--bs-gray-300);
    text-align: left;
}

.doc-content th {
    background: var(--bs-gray-100);
    font-weight: 600;
}

.doc-content tr:nth-child(even) {
    background: var(--bs-gray-50);
}

.doc-content code {
    background: var(--bs-gray-100);
    padding: 0.15rem 0.4rem;
    border-radius: 3px;
    font-size: 0.875em;
    color: var(--bs-danger);
}

.doc-content pre {
    background: var(--bs-gray-900);
    color: var(--bs-gray-100);
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
}

.doc-content pre code {
    background: transparent;
    color: inherit;
    padding: 0;
}

.doc-content blockquote {
    border-left: 4px solid var(--bs-primary);
    padding-left: 1rem;
    margin: 1rem 0;
    color: var(--bs-gray-600);
    font-style: italic;
}

.doc-content ul, .doc-content ol {
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

.doc-content li {
    margin-bottom: 0.25rem;
}

.doc-content hr {
    margin: 2rem 0;
    border-color: var(--bs-gray-300);
}

.doc-content a {
    color: var(--bs-primary);
}

/* TOC Navigation */
.toc-nav {
    max-height: 50vh;
    overflow-y: auto;
}

.toc-nav a:hover {
    color: var(--bs-primary) !important;
    background: var(--bs-gray-100);
}

/* Anchor links for headings */
.doc-content h2[id],
.doc-content h3[id] {
    scroll-margin-top: 80px;
}

/* Print styles */
@media print {
    .col-lg-3 { display: none !important; }
    .col-lg-9 { width: 100% !important; }
    .card-header button { display: none !important; }
}
</style>

<script>
// Add IDs to headings for anchor links
document.addEventListener('DOMContentLoaded', function() {
    const content = document.querySelector('.doc-content');
    const headings = content.querySelectorAll('h2, h3');
    
    headings.forEach(heading => {
        const text = heading.textContent;
        const id = text.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_]+/g, '-')
            .trim();
        heading.id = id;
    });
});
</script>
@endsection
