<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\Auditable; // Add this line

class Remark extends Model
{
    use HasFactory, Auditable; // Add this trait

    protected $fillable = [
        'job_id',
        'user_id',
        'remark_text',
        'images',
        'created_by',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    /**
     * Check if this remark has images attached
     */
    public function hasImages(): bool
    {
        return !empty($this->images) && is_array($this->images);
    }

    /**
     * Get full URLs for all images
     */
    public function getImageUrlsAttribute(): array
    {
        if (!$this->hasImages()) {
            return [];
        }

        return array_map(fn($path) => asset("storage/{$path}"), $this->images);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function containsOrderKeyword(): bool
    {
        return stripos($this->remark_text, 'ORDER') !== false;
    }

    /**
     * Get human-readable relative time (e.g., "2 hours ago")
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get the display name for the commenter
     */
    public function getCommenterNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }
        return $this->created_by ?? 'System';
    }

    /**
     * Get initials for avatar display
     */
    public function getCommenterInitialsAttribute(): string
    {
        $name = $this->commenter_name;
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }
}
