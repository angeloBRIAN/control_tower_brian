<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'rq',
        'no_order_part',
        'notes',
        'order_date',
        'expected_date',
        'ready_date',
        'received_date',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'ready_date' => 'date',
        'received_date' => 'date',
    ];

    // Status constants - workflow order (5-status flow: removed Ordering)
    const STATUS_PENDING = 'pending';       // Part needed, waiting for RQ form (Workshop)
    const STATUS_RQ_SENT = 'rq_sent';       // RQ form completed and sent to Sparepart (Workshop)
    const STATUS_PROCESSING = 'processing'; // Sparepart processing order (check stock, order from supplier) (Sparepart)
    const STATUS_READY = 'ready';           // Part available for pickup (Sparepart)
    const STATUS_RECEIVED = 'received';     // Part delivered to workshop/installed (Workshop)
    const STATUS_CANCELLED = 'cancelled';
    
    // Legacy status mappings (for backwards compatibility)
    const STATUS_BUKA_RQ = 'rq_sent';       // Alias for backwards compatibility
    const STATUS_ORDERING = 'processing';   // Ordering merged into Processing
    const STATUS_ORDERED = 'processing';    // Alias for backwards compatibility
    const STATUS_CONFIRMED = 'ready';       // Alias for backwards compatibility
    const STATUS_SHIPPED = 'ready';         // Alias for backwards compatibility
    const STATUS_INSTALLED = 'received';    // Alias for backwards compatibility

    /**
     * Get all available statuses in workflow order
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => ['label' => 'Pending', 'color' => '#f59e0b', 'icon' => 'bi-hourglass-split', 'owner' => 'Workshop'],
            self::STATUS_RQ_SENT => ['label' => 'RQ Sent', 'color' => '#06b6d4', 'icon' => 'bi-send', 'owner' => 'Workshop'],
            self::STATUS_PROCESSING => ['label' => 'Processing', 'color' => '#8b5cf6', 'icon' => 'bi-gear', 'owner' => 'Sparepart'],
            self::STATUS_READY => ['label' => 'Ready', 'color' => '#3b82f6', 'icon' => 'bi-check-circle', 'owner' => 'Sparepart'],
            self::STATUS_RECEIVED => ['label' => 'Received', 'color' => '#22c55e', 'icon' => 'bi-box-seam', 'owner' => 'Workshop'],
            self::STATUS_CANCELLED => ['label' => 'Cancelled', 'color' => '#ef4444', 'icon' => 'bi-x-circle', 'owner' => 'Any'],
        ];
    }

    /**
     * Get Kanban-displayable statuses (excludes cancelled)
     */
    public static function getKanbanStatuses(): array
    {
        $statuses = self::getStatuses();
        unset($statuses[self::STATUS_CANCELLED]);
        return $statuses;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status]['label'] ?? $this->status;
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return self::getStatuses()[$this->status]['color'] ?? '#6b7280';
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute(): string
    {
        return self::getStatuses()[$this->status]['icon'] ?? 'bi-circle';
    }

    /**
     * Calculate days until expected date
     */
    public function getDaysUntilExpectedAttribute(): int
    {
        if (!$this->expected_date) {
            return 0;
        }
        return now()->startOfDay()->diffInDays($this->expected_date, false);
    }

    /**
     * Check if overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->expected_date) {
            return false;
        }
        return $this->days_until_expected < 0 && 
               !in_array($this->status, [self::STATUS_RECEIVED, self::STATUS_INSTALLED, self::STATUS_CANCELLED]);
    }

    /**
     * Check if due soon (within 7 days)
     */
    public function getIsDueSoonAttribute(): bool
    {
        $days = $this->days_until_expected;
        return $days >= 0 && $days <= 7 && 
               !in_array($this->status, [self::STATUS_RECEIVED, self::STATUS_INSTALLED, self::STATUS_CANCELLED]);
    }

    // Relationships

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->whereNotIn('status', [self::STATUS_INSTALLED, self::STATUS_CANCELLED]);
    }

    public function scopeOverdue($query)
    {
        return $query->pending()
            ->where('expected_date', '<', now()->startOfDay());
    }

    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->pending()
            ->where('expected_date', '>=', now()->startOfDay())
            ->where('expected_date', '<=', now()->addDays($days)->endOfDay());
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
