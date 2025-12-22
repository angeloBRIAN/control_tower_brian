<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\Auditable;

class Vehicle extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'plate_number',
        'model',
        'year',
        'vin',
        'customer_name',
        'customer_phone',
        'is_in_workshop',
        'import_id',
    ];

    protected function casts(): array
    {
        return [
            'is_in_workshop' => 'boolean',
        ];
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'plate_number', 'plate_number');
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}
