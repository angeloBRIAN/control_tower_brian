<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'dms_magic',
        'name',
        'address_1',
        'address_2',
        'address_3',
        'address_4',
        'address_5',
        'phone',
        'email',
        'department',
        'dms_created_at',
        'dms_imported_at',
    ];

    protected $casts = [
        'dms_created_at' => 'date',
        'dms_imported_at' => 'datetime',
    ];

    /**
     * Customers associated with this company
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Vehicles owned by customers of this company
     */
    public function vehicles()
    {
        return Vehicle::whereIn('customer_id', $this->customers()->pluck('id'));
    }

    /**
     * Jobs from customers of this company
     */
    public function jobs()
    {
        return Job::whereIn('customer_id', $this->customers()->pluck('id'));
    }

    /**
     * Get full address combined
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_1,
            $this->address_2,
            $this->address_3,
            $this->address_4,
            $this->address_5,
        ]);
        return implode(', ', $parts);
    }
}
