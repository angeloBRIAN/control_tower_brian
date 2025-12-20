<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable; // Add this line

class Import extends Model
{
    use HasFactory, Auditable; // Add this trait

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
}
