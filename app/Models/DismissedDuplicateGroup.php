<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DismissedDuplicateGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_hash',
        'names',
        'dismissed_by',
        'reason',
    ];

    protected $casts = [
        'names' => 'array',
    ];

    /**
     * Generate a hash for a group of names (for consistent lookup)
     */
    public static function generateHash(array $names): string
    {
        // Normalize and sort names for consistent hashing
        $normalized = array_map(function($name) {
            return strtoupper(trim($name));
        }, $names);
        sort($normalized);
        return hash('sha256', implode('|', $normalized));
    }

    /**
     * Check if a group has been dismissed
     */
    public static function isDismissed(array $names): bool
    {
        $hash = self::generateHash($names);
        return self::where('group_hash', $hash)->exists();
    }

    /**
     * Dismiss a group
     */
    public static function dismiss(array $names, string $reason = 'not_duplicate', ?string $dismissedBy = null): self
    {
        return self::create([
            'group_hash' => self::generateHash($names),
            'names' => $names,
            'dismissed_by' => $dismissedBy ?? auth()->user()?->name ?? 'System',
            'reason' => $reason,
        ]);
    }
}
