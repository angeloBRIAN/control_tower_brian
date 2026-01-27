<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAlias extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'alias_name',
        'created_by',
    ];

    protected static $allCustomersCache = null;
    protected static $allAliasesCache = null;

    /**
     * The customer this alias belongs to
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The user who created this alias
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Find customer by name or alias - uses normalized matching
     * Optionally falls back to vehicle lookup if plate_number/chassis provided
     */
    public static function findCustomerByName(string $name, ?string $plateNumber = null, ?string $chassis = null): ?Customer
    {
        $exactName = strtoupper(trim($name));
        $normalizedName = \App\Helpers\CustomerNameHelper::normalize($name);
        
        // 1. Try exact match on customers table
        $customer = Customer::whereRaw('UPPER(name) = ?', [$exactName])->first();
        if ($customer) {
            return $customer;
        }
        
        // 2. Try normalized match (e.g., "PT ABC" matches "ABC" with title="PT")
        if (self::$allCustomersCache === null) {
            self::$allCustomersCache = Customer::whereNotNull('name')->get();
        }
        $allCustomers = self::$allCustomersCache;

        foreach ($allCustomers as $c) {
            if (\App\Helpers\CustomerNameHelper::normalize($c->name) === $normalizedName) {
                return $c;
            }
            // Also try with title + name
            if ($c->title) {
                $fullName = strtoupper(trim($c->title . ' ' . $c->name));
                if ($fullName === $exactName) {
                    return $c;
                }
            }
        }
        
        // 3. Try alias table (exact first, then normalized)
        $alias = self::whereRaw('UPPER(alias_name) = ?', [$exactName])->first();
        if ($alias) {
            return $alias->customer;
        }
        
        // Normalized alias match
        if (self::$allAliasesCache === null) {
            self::$allAliasesCache = self::with('customer')->get();
        }
        $allAliases = self::$allAliasesCache;

        foreach ($allAliases as $a) {
            if (\App\Helpers\CustomerNameHelper::normalize($a->alias_name) === $normalizedName) {
                return $a->customer;
            }
        }
        
        // 4. Vehicle-based fallback - look up customer via vehicle
        if ($plateNumber || $chassis) {
            $vehicleCustomer = self::findCustomerByVehicle($plateNumber, $chassis);
            if ($vehicleCustomer) {
                return $vehicleCustomer;
            }
        }
        
        return null;
    }

    /**
     * Find customer by vehicle plate number or chassis
     * Useful when customer name from invoice doesn't match DMS but vehicle does
     */
    public static function findCustomerByVehicle(?string $plateNumber, ?string $chassis = null): ?Customer
    {
        $vehicle = null;
        
        // Try plate number first
        if ($plateNumber && trim($plateNumber) !== '') {
            $vehicle = Vehicle::where('plate_number', $plateNumber)
                ->whereNotNull('customer_id')
                ->first();
        }
        
        // Fallback to chassis/VIN
        if (!$vehicle && $chassis && trim($chassis) !== '') {
            $vehicle = Vehicle::where('vin', $chassis)
                ->whereNotNull('customer_id')
                ->first();
        }
        
        if ($vehicle && $vehicle->customer_id) {
            return Customer::find($vehicle->customer_id);
        }
        
        return null;
    }

    /**
     * Get unmatched customer names from jobs
     */
    public static function getUnmatchedNames(): \Illuminate\Support\Collection
    {
        return \DB::table('jobs')
            ->whereNull('customer_id')
            ->whereNotNull('customer_name')
            ->select('customer_name')
            ->distinct()
            ->orderBy('customer_name')
            ->pluck('customer_name');
    }
}
