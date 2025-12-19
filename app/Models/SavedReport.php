<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedReport extends Model
{
    use HasFactory;

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
