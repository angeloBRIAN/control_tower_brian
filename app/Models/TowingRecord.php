<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TowingRecord extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'plate_number',
        'customer_name',
        'customer_phone',
        'pickup_location',
        'scheduled_date',
        'scheduled_time',
        'job_type',
        'status',
        'notes',
        'import_id',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime:H:i',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}
