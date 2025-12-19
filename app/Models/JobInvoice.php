<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class JobInvoice extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'job_id',
        'invoice_number',
        'invoice_date',
        'invoice_type',
        'inv_amount',
        'inv_ppn',
        'inv_ppn_meterai',
        'type_sale',
        'notes',
        'import_id',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'inv_amount' => 'decimal:2',
        'inv_ppn' => 'decimal:2',
        'inv_ppn_meterai' => 'decimal:2',
    ];

    /**
     * Get the job this invoice belongs to
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the import this invoice came from
     */
    public function import()
    {
        return $this->belongsTo(Import::class);
    }

    /**
     * Check if this is a credit note
     */
    public function isCreditNote(): bool
    {
        return $this->invoice_type === 'credit_note';
    }

    /**
     * Get the effective amount (negative for CN)
     */
    public function getEffectiveAmountAttribute(): float
    {
        $amount = (float) $this->inv_ppn_meterai;
        return $this->isCreditNote() ? -abs($amount) : $amount;
    }

    /**
     * Get formatted type sale label
     */
    public function getTypeSaleLabelAttribute(): string
    {
        return match(strtoupper($this->type_sale ?? '')) {
            'INT' => 'Internal',
            'WAR' => 'Warranty',
            'CASH' => 'Cash',
            default => $this->type_sale ?? '-',
        };
    }
}
