<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSummary extends Model
{
    protected $fillable = [
        'name',
        'customer_id',
        'dms_magic',
        'email',
        'phone',
        'company_name',
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

    /**
     * Linked customer record (DMS imported)
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Check if this summary is linked to DMS customer
     */
    public function getIsDmsLinkedAttribute(): bool
    {
        return $this->customer_id !== null;
    }
}

