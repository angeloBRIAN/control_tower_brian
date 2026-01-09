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
     */
    public static function findCustomerByName(string $name): ?Customer
    {
        $exactName = strtoupper(trim($name));
        $normalizedName = \App\Helpers\CustomerNameHelper::normalize($name);
        
        // 1. Try exact match on customers table
        $customer = Customer::whereRaw('UPPER(name) = ?', [$exactName])->first();
        if ($customer) {
            return $customer;
        }
        
        // 2. Try normalized match (e.g., "PT ABC" matches "ABC" with title="PT")
        $allCustomers = Customer::whereNotNull('name')->get();
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
        $allAliases = self::with('customer')->get();
        foreach ($allAliases as $a) {
            if (\App\Helpers\CustomerNameHelper::normalize($a->alias_name) === $normalizedName) {
                return $a->customer;
            }
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
