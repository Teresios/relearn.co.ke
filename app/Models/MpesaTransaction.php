<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class MpesaTransaction extends Model
{
    protected $fillable = [
        'transaction_id',
        'conversation_id',
        'originator_conversation_id',
        'checkout_request_id',
        'merchant_request_id',
        'type',
        'direction',
        'amount',
        'currency',
        'sender_phone',
        'receiver_phone',
        'sender_shortcode',
        'receiver_shortcode',
        'account_reference',
        'transaction_desc',
        'status',
        'result_code',
        'result_desc',
        'is_reversed',
        'reversed_transaction_id',
        'reversed_at',
        'reversal_reason',
        'is_forwarded',
        'forwarded_transaction_id',
        'forwarded_at',
        'balance_after',
        'request_payload',
        'response_payload',
        'callback_payload',
        'user_id',
        'order_id',
        'initiated_by',
        'ip_address',
        'transaction_time',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'callback_payload' => 'array',
        'is_reversed' => 'boolean',
        'is_forwarded' => 'boolean',
        'reversed_at' => 'datetime',
        'forwarded_at' => 'datetime',
        'transaction_time' => 'datetime',
    ];

    // Transaction types
    const TYPE_C2B = 'c2b';
    const TYPE_B2C = 'b2c';
    const TYPE_B2B = 'b2b';
    const TYPE_STK_PUSH = 'stk_push';
    const TYPE_REVERSAL = 'reversal';

    // Directions
    const DIRECTION_INBOUND = 'inbound';
    const DIRECTION_OUTBOUND = 'outbound';

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REVERSED = 'reversed';

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function reversedTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversed_transaction_id');
    }

    public function reversal(): HasOne
    {
        return $this->hasOne(self::class, 'reversed_transaction_id');
    }

    public function forwardedTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'forwarded_transaction_id');
    }

    public function forwardedFrom(): HasOne
    {
        return $this->hasOne(self::class, 'forwarded_transaction_id');
    }

    // Scopes
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', self::DIRECTION_INBOUND);
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', self::DIRECTION_OUTBOUND);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ]);
    }

    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    // Helpers
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isInbound(): bool
    {
        return $this->direction === self::DIRECTION_INBOUND;
    }

    public function isOutbound(): bool
    {
        return $this->direction === self::DIRECTION_OUTBOUND;
    }

    public function canBeReversed(): bool
    {
        return $this->isSuccessful() && !$this->is_reversed && in_array($this->type, [self::TYPE_B2C, self::TYPE_B2B]);
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_C2B => 'Customer to Business',
            self::TYPE_B2C => 'Business to Customer',
            self::TYPE_B2B => 'Business to Business',
            self::TYPE_STK_PUSH => 'STK Push',
            self::TYPE_REVERSAL => 'Reversal',
            default => ucfirst($this->type),
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_SUCCESS => 'success',
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            self::STATUS_REVERSED => 'dark',
            default => 'secondary',
        };
    }

    public function getFormattedAmount(): string
    {
        $prefix = $this->isOutbound() ? '-' : '+';
        return $prefix . ' KES ' . number_format($this->amount, 2);
    }

    // Static methods for dashboard
    public static function getTodayStats(): array
    {
        return [
            'total_inbound' => self::today()->inbound()->successful()->sum('amount'),
            'total_outbound' => self::today()->outbound()->successful()->sum('amount'),
            'transaction_count' => self::today()->count(),
            'success_count' => self::today()->successful()->count(),
            'failed_count' => self::today()->failed()->count(),
        ];
    }

    public static function getWeeklyStats(): array
    {
        return [
            'total_inbound' => self::thisWeek()->inbound()->successful()->sum('amount'),
            'total_outbound' => self::thisWeek()->outbound()->successful()->sum('amount'),
            'transaction_count' => self::thisWeek()->count(),
            'success_count' => self::thisWeek()->successful()->count(),
            'failed_count' => self::thisWeek()->failed()->count(),
        ];
    }

    public static function getMonthlyStats(): array
    {
        return [
            'total_inbound' => self::thisMonth()->inbound()->successful()->sum('amount'),
            'total_outbound' => self::thisMonth()->outbound()->successful()->sum('amount'),
            'transaction_count' => self::thisMonth()->count(),
            'success_count' => self::thisMonth()->successful()->count(),
            'failed_count' => self::thisMonth()->failed()->count(),
        ];
    }
}
