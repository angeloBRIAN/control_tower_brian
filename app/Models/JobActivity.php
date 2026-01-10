<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobActivity extends Model
{
    protected $fillable = [
        'job_id',
        'user_id',
        'user_name',
        'action',
        'description',
        'changes',
        'ip_address',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * Actions that can be logged.
     */
    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_STATUS_CHANGED = 'status_changed';
    const ACTION_WORK_STATUS_CHANGED = 'work_status_changed';
    const ACTION_REMARK_ADDED = 'remark_added';
    const ACTION_INVOICED = 'invoiced';
    const ACTION_UNINVOICED = 'uninvoiced';
    const ACTION_PARTS_UPDATED = 'parts_updated';
    const ACTION_IMPORT_CREATED = 'import_created';
    const ACTION_IMPORT_UPDATED = 'import_updated';
    const ACTION_INVOICE_CREATED = 'invoice_import_created';
    const ACTION_INVOICE_UPDATED = 'invoice_import_updated';

    /**
     * Get the job this activity belongs to.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an activity for a job.
     */
    public static function log(Job $job, string $action, string $description, ?array $changes = null): self
    {
        $user = auth()->user();
        
        return self::create([
            'job_id' => $job->id,
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'action' => $action,
            'description' => $description,
            'changes' => $changes,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Get icon for activity type.
     */
    public function getIconAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'plus-circle',
            self::ACTION_UPDATED => 'pencil',
            self::ACTION_STATUS_CHANGED => 'arrow-repeat',
            self::ACTION_WORK_STATUS_CHANGED => 'gear',
            self::ACTION_REMARK_ADDED => 'chat-dots',
            self::ACTION_INVOICED => 'check-circle',
            self::ACTION_UNINVOICED => 'x-circle',
            self::ACTION_PARTS_UPDATED => 'tools',
            self::ACTION_IMPORT_CREATED => 'cloud-arrow-up',
            self::ACTION_IMPORT_UPDATED => 'cloud-check',
            self::ACTION_INVOICE_CREATED => 'receipt',
            self::ACTION_INVOICE_UPDATED => 'receipt-cutoff',
            default => 'circle',
        };
    }

    /**
     * Get color for activity type.
     */
    public function getColorAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'success',
            self::ACTION_INVOICED => 'success',
            self::ACTION_UPDATED => 'primary',
            self::ACTION_STATUS_CHANGED => 'info',
            self::ACTION_WORK_STATUS_CHANGED => 'warning',
            self::ACTION_REMARK_ADDED => 'secondary',
            self::ACTION_UNINVOICED => 'danger',
            self::ACTION_PARTS_UPDATED => 'info',
            self::ACTION_IMPORT_CREATED => 'success',
            self::ACTION_IMPORT_UPDATED => 'primary',
            self::ACTION_INVOICE_CREATED => 'success',
            self::ACTION_INVOICE_UPDATED => 'info',
            default => 'secondary',
        };
    }
}
