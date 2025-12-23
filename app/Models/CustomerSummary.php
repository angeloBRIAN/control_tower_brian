<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSummary extends Model
{
    protected $fillable = [
        'name',
        'vehicle_count',
        'job_count',
        'uninvoiced_count',
        'invoiced_count',
        'total_sales',
        'estimated_sales',
    ];

    protected $casts = [
        'total_sales' => 'decimal:2',
        'estimated_sales' => 'decimal:2',
    ];
}
