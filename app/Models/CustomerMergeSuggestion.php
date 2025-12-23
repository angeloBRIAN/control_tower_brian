<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMergeSuggestion extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_MERGED = 'merged';
    const STATUS_IGNORED = 'ignored';

    protected $fillable = [
        'customer_name_a',
        'customer_name_b',
        'similarity_score',
        'jobs_count_a',
        'jobs_count_b',
        'status',
    ];

    /**
     * Scope for pending suggestions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Get the total jobs affected
     */
    public function getTotalJobsAttribute(): int
    {
        return $this->jobs_count_a + $this->jobs_count_b;
    }
}
