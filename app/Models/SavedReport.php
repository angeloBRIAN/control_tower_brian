<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class SavedReport extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'user_id',
        'data_source',
        'columns',
        'filters',
    ];

    protected $casts = [
        'columns' => 'array',
        'filters' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
