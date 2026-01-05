<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecentlyViewed extends Model
{
    protected $table = 'recently_viewed';
    
    protected $fillable = [
        'user_id',
        'job_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * Get the user that viewed the job.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the job that was viewed.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Record a job view for a user.
     * Updates timestamp if already exists, creates if new.
     * Limits to 10 recent items per user.
     */
    public static function recordView(int $userId, int $jobId): void
    {
        static::updateOrCreate(
            ['user_id' => $userId, 'job_id' => $jobId],
            ['viewed_at' => now()]
        );

        // Keep only the 10 most recent views per user
        $oldViews = static::where('user_id', $userId)
            ->orderBy('viewed_at', 'desc')
            ->skip(10)
            ->take(100)
            ->pluck('id');

        if ($oldViews->isNotEmpty()) {
            static::whereIn('id', $oldViews)->delete();
        }
    }

    /**
     * Get recent jobs for a user.
     */
    public static function getRecentForUser(int $userId, int $limit = 5)
    {
        return static::where('user_id', $userId)
            ->with('job:id,job_number,plate_number,customer_name,status')
            ->orderBy('viewed_at', 'desc')
            ->limit($limit)
            ->get()
            ->pluck('job')
            ->filter(); // Remove nulls if jobs were deleted
    }
}
