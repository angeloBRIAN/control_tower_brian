<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class BackupSchedule extends Model
{
    use HasFactory, Auditable;
    
    protected $guarded = [];
    
    protected $casts = [
        'enabled' => 'boolean',
        'prune_enabled' => 'boolean',
        'audit_archive_enabled' => 'boolean',
    ];
}
