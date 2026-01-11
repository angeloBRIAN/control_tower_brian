<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Announcement Model.
 * 
 * Broadcast announcements displayed on all users' dashboards.
 * 
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $author_id
 * @property bool $is_important
 * @property bool $is_pinned
 * @property bool $send_push
 * @property array|null $target_roles
 * @property \Carbon\Carbon|null $published_at
 * @property \Carbon\Carbon|null $expires_at
 * @property array|null $dismissed_by
 */
class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'author_id',
        'is_important',
        'is_pinned',
        'send_push',
        'target_roles',
        'published_at',
        'expires_at',
        'dismissed_by',
    ];

    protected $casts = [
        'is_important' => 'boolean',
        'is_pinned' => 'boolean',
        'send_push' => 'boolean',
        'target_roles' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'dismissed_by' => 'array',
    ];

    /**
     * Author relationship.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Active announcements (published and not expired).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Pinned announcements first.
     */
    public function scopePinned(Builder $query): Builder
    {
        return $query->orderByDesc('is_pinned');
    }

    /**
     * Filter by role.
     */
    public function scopeForRole(Builder $query, string $role): Builder
    {
        return $query->where(function ($q) use ($role) {
            $q->whereNull('target_roles')
              ->orWhereJsonContains('target_roles', $role);
        });
    }

    /**
     * Exclude dismissed by user.
     */
    public function scopeNotDismissedBy(Builder $query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('dismissed_by')
              ->orWhereRaw('NOT JSON_CONTAINS(dismissed_by, ?)', [json_encode($userId)]);
        });
    }

    /**
     * Check if dismissed by user.
     */
    public function isDismissedBy(User $user): bool
    {
        $dismissed = $this->dismissed_by ?? [];
        return in_array($user->id, $dismissed);
    }

    /**
     * Dismiss for a user.
     */
    public function dismiss(User $user): void
    {
        $dismissed = $this->dismissed_by ?? [];
        if (!in_array($user->id, $dismissed)) {
            $dismissed[] = $user->id;
            $this->update(['dismissed_by' => $dismissed]);
        }
    }

    /**
     * Get active announcements for a user.
     */
    public static function getForUser(User $user, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return self::query()
            ->active()
            ->forRole($user->role)
            ->notDismissedBy($user->id)
            ->pinned()
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Broadcast to all target users via push notification.
     */
    public function broadcast(): int
    {
        if (!$this->send_push) {
            return 0;
        }

        $query = User::query();
        
        // Filter by target roles if specified
        if (!empty($this->target_roles)) {
            $query->whereIn('role', $this->target_roles);
        }
        
        $users = $query->get();
        $count = 0;
        
        foreach ($users as $user) {
            // Create notification record
            Notification::notify(
                userId: $user->id,
                type: Notification::TYPE_SYSTEM,
                title: '📢 ' . $this->title,
                message: strip_tags(substr($this->content, 0, 100)) . '...',
                link: route('dashboard'),
                icon: 'megaphone-fill',
                color: $this->is_important ? 'danger' : 'info',
                data: ['announcement_id' => $this->id]
            );
            $count++;
        }
        
        return $count;
    }

    /**
     * Permission helper - can user create announcements?
     */
    public static function canCreate(User $user): bool
    {
        // Check role permissions
        $permissions = config('permissions.roles.' . $user->role, []);
        return in_array('broadcast_announcements', $permissions) || $user->role === 'admin';
    }
}
