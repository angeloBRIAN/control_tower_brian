<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Announcement Controller.
 * 
 * Manages broadcast announcements that are displayed to all users.
 */
class AnnouncementController extends Controller
{
    /**
     * Check if user can manage announcements.
     */
    protected function authorize(): void
    {
        if (!Announcement::canCreate(auth()->user())) {
            abort(403, 'You do not have permission to manage announcements.');
        }
    }

    /**
     * Display list of announcements.
     */
    public function index(): View
    {
        $this->authorize();
        
        $announcements = Announcement::with('author')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(15);
            
        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        $this->authorize();
        
        $roles = User::select('role')
            ->distinct()
            ->pluck('role')
            ->toArray();
            
        return view('admin.announcements.create', compact('roles'));
    }

    /**
     * Store new announcement.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize();
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_important' => 'boolean',
            'is_pinned' => 'boolean',
            'send_push' => 'boolean',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'string',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);
        
        $announcement = Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'author_id' => auth()->id(),
            'is_important' => $validated['is_important'] ?? false,
            'is_pinned' => $validated['is_pinned'] ?? false,
            'send_push' => $validated['send_push'] ?? true,
            'target_roles' => !empty($validated['target_roles']) ? $validated['target_roles'] : null,
            'published_at' => $validated['published_at'] ?? now(),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);
        
        // Broadcast push notifications
        $notifiedCount = $announcement->broadcast();
        
        return redirect()
            ->route('admin.announcements.index')
            ->with('success', "Announcement created! Notified {$notifiedCount} users.");
    }

    /**
     * Show edit form.
     */
    public function edit(Announcement $announcement): View
    {
        $this->authorize();
        
        $roles = User::select('role')
            ->distinct()
            ->pluck('role')
            ->toArray();
            
        return view('admin.announcements.edit', compact('announcement', 'roles'));
    }

    /**
     * Update announcement.
     */
    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize();
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_important' => 'boolean',
            'is_pinned' => 'boolean',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'string',
            'expires_at' => 'nullable|date',
        ]);
        
        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_important' => $validated['is_important'] ?? false,
            'is_pinned' => $validated['is_pinned'] ?? false,
            'target_roles' => !empty($validated['target_roles']) ? $validated['target_roles'] : null,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);
        
        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Delete announcement.
     */
    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize();
        
        $announcement->delete();
        
        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement deleted.');
    }

    /**
     * Dismiss announcement for current user.
     */
    public function dismiss(Announcement $announcement): RedirectResponse
    {
        $announcement->dismiss(auth()->user());
        
        return back()->with('info', 'Announcement dismissed.');
    }
    
    /**
     * Dismiss via AJAX.
     */
    public function dismissAjax(Announcement $announcement)
    {
        $announcement->dismiss(auth()->user());
        
        return response()->json(['success' => true]);
    }

    /**
     * Resend push notifications.
     */
    public function resend(Announcement $announcement): RedirectResponse
    {
        $this->authorize();
        
        $count = $announcement->broadcast();
        
        return back()->with('success', "Resent notification to {$count} users.");
    }
}
