<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\Auditable; // Add this line

class ServiceAdvisor extends Model
{
    use HasFactory, Auditable; // Add this trait

    protected $fillable = ['name', 'franchise', 'active', 'user_id'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
