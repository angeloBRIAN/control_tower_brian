<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable;

class Import extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'file_name',
        'file_path',
        'import_type',
        'records_imported',
        'records_updated',
        'records_failed',
        'failed_rows',
        'conflict_rows',
        'notes',
        'imported_by',
    ];

    protected $casts = [
        'failed_rows' => 'array',
        'conflict_rows' => 'array',
    ];

    /**
     * User who performed the import
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->import_type) {
            'progress' => 'Progress',
            'uninvoiced' => 'Uninvoiced',
            'invoiced' => 'Invoiced',
            'dms_customers' => 'DMS Customers',
            'dms_vehicles' => 'DMS Vehicles',
            default => ucfirst($this->import_type),
        };
    }

    /**
     * Get type color for badge
     */
    public function getTypeColorAttribute(): string
    {
        return match($this->import_type) {
            'progress' => 'primary',
            'uninvoiced' => 'warning',
            'invoiced' => 'success',
            'dms_customers' => 'info',
            'dms_vehicles' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Check if this is a DMS import type
     */
    public function getIsDmsAttribute(): bool
    {
        return str_starts_with($this->import_type, 'dms_');
    }
}

