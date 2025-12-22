<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdiRecord extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'plate_number',
        'vin',
        'engine_no',
        'wip',
        'model',
        'colour',
        'pdi_date',
        'technician',
        'status',
        'notes',
        'import_id',
    ];

    protected function casts(): array
    {
        return [
            'pdi_date' => 'date',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}
