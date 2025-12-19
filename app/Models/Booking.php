<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'plate_number',
        'wip',
        'customer_name',
        'customer_phone',
        'booking_date',
        'booking_time',
        'service_type',
        'foreman',
        'service_advisor',
        'notes',
        'status',
        'import_id',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}
