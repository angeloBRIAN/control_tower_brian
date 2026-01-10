<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class JobInvoice extends Model
{
    use HasFactory, Auditable;

    // Invoice Status Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PARTIALLY_PAID = 'partially_paid';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        'draft' => 'Draft',
        'pending' => 'Pending Payment',
        'partially_paid' => 'Partially Paid',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'job_id',
        'invoice_number',
        'invoice_date',
        'invoice_type',
        'status',
        'inv_amount',
        'inv_ppn',
        'inv_ppn_meterai',
        'paid_amount',
        'paid_at',
        'type_sale',
        'notes',
        'finance_remark',
        'import_id',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'paid_at' => 'datetime',
            'inv_amount' => 'decimal:2',
            'inv_ppn' => 'decimal:2',
            'inv_ppn_meterai' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    /**
     * Boot the model - handle status change side effects
     */
    protected static function booted(): void
    {
        static::updated(function (JobInvoice $invoice) {
            // When status changes, check if we need to update Job work_status
            if ($invoice->wasChanged('status')) {
                $invoice->syncJobWorkStatus();
            }
        });
    }

    /**
     * Sync parent Job's work_status based on invoice statuses
     */
    public function syncJobWorkStatus(): void
    {
        $job = $this->job;
        if (!$job) return;

        // Check if first invoice is being created/activated
        if ($this->status === self::STATUS_PENDING) {
            // Move job to Step 11 (Proses Invoice) if not already past it
            $currentStep = array_search($job->work_status, Job::WORK_STATUSES);
            if ($currentStep !== false && $currentStep < 10) {
                $job->update(['work_status' => Job::WORK_STATUSES[10]]); // 11. Proses Invoice
            }
        }

        // Check if all invoices are paid
        if ($this->status === self::STATUS_PAID) {
            $unpaidCount = $job->invoices()
                ->where('invoice_type', 'invoice')
                ->whereNotIn('status', [self::STATUS_PAID, self::STATUS_CANCELLED])
                ->count();

            if ($unpaidCount === 0) {
                // All invoices paid - move to Step 13 (Sudah Dibayar)
                $job->update(['work_status' => Job::WORK_STATUSES[12]]); // 13. Sudah Dibayar
            }
        }
    }

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
