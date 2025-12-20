<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropdownOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'value',
        'label',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get active options for a specific type
     */
    public static function getOptions(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('type', $type)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }

    /**
     * Get options as array for dropdown
     */
    public static function getOptionsArray(string $type): array
    {
        return static::getOptions($type)
            ->pluck('label', 'value')
            ->toArray();
    }

    /**
     * Get option by type and value
     */
    public static function getOption(string $type, ?string $value): ?self
    {
        if (!$value) return null;
        return static::where('type', $type)
            ->where('value', $value)
            ->first();
    }

    /**
     * Available dropdown types
     */
    public static function getTypes(): array
    {
        return [
            'work_status' => 'Work Status',
            'payment_type' => 'Payment Type',
            'technician' => 'Technician',
            'block' => 'Block/Bay',
        ];
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return static::getTypes()[$this->type] ?? $this->type;
    }
}
