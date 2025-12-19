<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class CustomerMergeLog extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'old_name',
        'canonical_name',
        'source_type',
        'jobs_updated',
        'vehicles_updated',
        'merged_by',
        'notes',
    ];

    /**
     * Get source type label
     */
    public function getSourceTypeLabelAttribute(): string
    {
        return match($this->source_type) {
            'dms_import' => 'DMS Import (Uninvoiced/Invoiced)',
            'job_progress_import' => 'Job Progress Import',
            'user_entry' => 'User Entry (Manual)',
            default => $this->source_type ?? 'Unknown',
        };
    }

    /**
     * Scope for DMS import duplicates (need to fix in main system)
     */
    public function scopeDmsImport($query)
    {
        return $query->where('source_type', 'dms_import');
    }

    /**
     * Scope for user entry duplicates (user mistake)
     */
    public function scopeUserMistake($query)
    {
        return $query->whereIn('source_type', ['job_progress_import', 'user_entry']);
    }
}
