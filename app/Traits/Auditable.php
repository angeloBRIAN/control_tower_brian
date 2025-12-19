<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable(): void
    {
        // Log when a model is created
        static::created(function ($model) {
            $model->logAudit('created', null, $model->getAttributes());
        });

        // Log when a model is updated
        static::updated(function ($model) {
            $dirty = $model->getDirty();
            if (!empty($dirty)) {
                $original = array_intersect_key($model->getOriginal(), $dirty);
                $model->logAudit('updated', $original, $dirty);
            }
        });

        // Log when a model is deleted
        static::deleted(function ($model) {
            $model->logAudit('deleted', $model->getOriginal(), null);
        });
    }

    /**
     * Create an audit log entry
     */
    protected function logAudit(string $action, ?array $oldValues, ?array $newValues): void
    {
        // Don't log if it's during seeding or migration
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            // Still log but might not have user
        }

        AuditLog::create([
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'user_id' => Auth::id(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Get all audit logs for this model
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
