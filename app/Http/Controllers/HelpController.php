<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Parsedown;

class HelpController extends Controller
{
    protected $parsedown;
    
    // Map of document slugs to file paths and titles
    protected $documents = [
        'documentation' => [
            'file' => 'DOCUMENTATION.md',
            'title' => 'Comprehensive Documentation',
            'icon' => 'bi-book',
            'description' => 'Complete feature reference and system overview',
        ],
        'dashboard-widgets' => [
            'file' => 'DASHBOARD_WIDGETS.md',
            'title' => 'Dashboard & Widgets',
            'icon' => 'bi-grid-1x2-fill',
            'description' => 'Customize your dashboard with 28 available widgets',
        ],
        'announcements' => [
            'file' => 'ANNOUNCEMENTS.md',
            'title' => 'Announcements & Broadcasts',
            'icon' => 'bi-megaphone-fill',
            'description' => 'Send broadcast messages to all users',
        ],
        'workflows' => [
            'file' => 'WORKFLOW_GUIDE.md',
            'title' => 'Workflow Guide',
            'icon' => 'bi-diagram-3',
            'description' => 'Step-by-step operational workflows',
        ],
        'functions' => [
            'file' => 'FUNCTION_REFERENCE.md',
            'title' => 'Function Reference',
            'icon' => 'bi-code-slash',
            'description' => 'Technical API reference for developers',
        ],
        'roles' => [
            'file' => 'ROLE_PERMISSIONS.md',
            'title' => 'Role Permissions',
            'icon' => 'bi-shield-lock',
            'description' => 'User roles and permission system',
        ],
        'deployment' => [
            'file' => 'DEPLOYMENT_GUIDE.md',
            'title' => 'Deployment Guide',
            'icon' => 'bi-cloud-upload',
            'description' => 'Installation and deployment instructions',
        ],
    ];

    public function __construct()
    {
        $this->parsedown = new Parsedown();
        $this->parsedown->setSafeMode(true);
    }

    /**
     * Help center index page
     */
    public function index()
    {
        $documents = $this->documents;
        
        // Quick reference shortcuts
        $shortcuts = [
            ['key' => 'Ctrl+K / S', 'action' => 'Focus search'],
            ['key' => 'N', 'action' => 'New job'],
            ['key' => 'G → D', 'action' => 'Go to Dashboard'],
            ['key' => 'G → J', 'action' => 'Go to Jobs'],
            ['key' => 'G → R', 'action' => 'Go to Reports'],
            ['key' => 'G → C', 'action' => 'Go to Customers'],
            ['key' => '?', 'action' => 'Show shortcuts help'],
            ['key' => 'Esc', 'action' => 'Close modal/search'],
        ];

        return view('help.index', compact('documents', 'shortcuts'));
    }

    /**
     * Display a specific documentation page
     */
    public function show(string $slug)
    {
        if (!isset($this->documents[$slug])) {
            abort(404, 'Documentation not found');
        }

        $doc = $this->documents[$slug];
        $filePath = base_path('docs/' . $doc['file']);

        if (!file_exists($filePath)) {
            abort(404, 'Documentation file not found');
        }

        $markdown = file_get_contents($filePath);
        $content = $this->parsedown->text($markdown);
        
        // Extract table of contents from h2 and h3 headers
        $toc = $this->extractTableOfContents($markdown);

        return view('help.show', [
            'title' => $doc['title'],
            'icon' => $doc['icon'],
            'content' => $content,
            'toc' => $toc,
            'documents' => $this->documents,
            'currentSlug' => $slug,
        ]);
    }

    /**
     * Extract table of contents from markdown
     */
    protected function extractTableOfContents(string $markdown): array
    {
        $toc = [];
        $lines = explode("\n", $markdown);
        
        foreach ($lines as $line) {
            if (preg_match('/^##\s+(.+)$/', $line, $matches)) {
                $title = trim($matches[1]);
                $slug = $this->slugify($title);
                $toc[] = [
                    'level' => 2,
                    'title' => strip_tags($title),
                    'slug' => $slug,
                ];
            } elseif (preg_match('/^###\s+(.+)$/', $line, $matches)) {
                $title = trim($matches[1]);
                $slug = $this->slugify($title);
                $toc[] = [
                    'level' => 3,
                    'title' => strip_tags($title),
                    'slug' => $slug,
                ];
            }
        }

        return $toc;
    }

    /**
     * Create URL-friendly slug from title
     */
    protected function slugify(string $text): string
    {
        // Remove special characters, keep alphanumeric and spaces
        $text = preg_replace('/[^\w\s-]/', '', $text);
        // Replace spaces with hyphens
        $text = preg_replace('/[\s_]+/', '-', $text);
        // Convert to lowercase
        return strtolower(trim($text, '-'));
    }
}
