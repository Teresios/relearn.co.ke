<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MpesaForwardingRule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'source_phone',
        'source_shortcode',
        'destination_shortcode',
        'destination_account',
        'min_amount',
        'max_amount',
        'forward_percentage',
        'flat_fee',
        'auto_forward',
        'delay_seconds',
        'active_hours',
        'active_days',
        'is_active',
        'total_forwarded_count',
        'total_forwarded_amount',
        'last_forward_at',
        'failed_forward_count',
        'last_failed_at',
        'last_error',
        'created_by',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'forward_percentage' => 'decimal:2',
        'flat_fee' => 'decimal:2',
        'total_forwarded_amount' => 'decimal:2',
        'active_hours' => 'array',
        'active_days' => 'array',
        'auto_forward' => 'boolean',
        'is_active' => 'boolean',
        'last_forward_at' => 'datetime',
        'last_failed_at' => 'datetime',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAutoForward($query)
    {
        return $query->where('auto_forward', true);
    }

    public function scopeForPhone($query, string $phone)
    {
        return $query->where('source_phone', $phone);
    }

    // Helper methods
    public function matchesTransaction(MpesaTransaction $transaction): bool
    {
        // Check if transaction is inbound
        if (!$transaction->isInbound()) {
            return false;
        }

        // Check source phone match
        if ($this->source_phone && $transaction->sender_phone !== $this->source_phone) {
            return false;
        }

        // Check source shortcode match
        if ($this->source_shortcode && $transaction->receiver_shortcode !== $this->source_shortcode) {
            return false;
        }

        // Check amount range
        if ($this->min_amount && $transaction->amount < $this->min_amount) {
            return false;
        }

        if ($this->max_amount && $transaction->amount > $this->max_amount) {
            return false;
        }

        // Check if within active hours
        if (!$this->isWithinActiveHours()) {
            return false;
        }

        // Check if on active day
        if (!$this->isOnActiveDay()) {
            return false;
        }

        return true;
    }

    public function isWithinActiveHours(): bool
    {
        if (empty($this->active_hours)) {
            return true;
        }

        $now = Carbon::now();
        $start = Carbon::parse($this->active_hours['start'] ?? '00:00');
        $end = Carbon::parse($this->active_hours['end'] ?? '23:59');

        return $now->between($start, $end);
    }

    public function isOnActiveDay(): bool
    {
        if (empty($this->active_days)) {
            return true;
        }

        return in_array(Carbon::now()->dayOfWeek, $this->active_days);
    }

    public function calculateForwardAmount(float $amount): float
    {
        $forwardAmount = ($amount * $this->forward_percentage / 100) - $this->flat_fee;
        return max(0, $forwardAmount);
    }

    public function recordSuccess(float $amount): void
    {
        $this->increment('total_forwarded_count');
        $this->increment('total_forwarded_amount', $amount);
        $this->update(['last_forward_at' => now()]);
    }

    public function recordFailure(string $error): void
    {
        $this->increment('failed_forward_count');
        $this->update([
            'last_failed_at' => now(),
            'last_error' => $error,
        ]);
    }

    public function getSuccessRate(): float
    {
        $total = $this->total_forwarded_count + $this->failed_forward_count;
        if ($total === 0) return 100;
        
        return round(($this->total_forwarded_count / $total) * 100, 2);
    }

    public function getStatusBadgeClass(): string
    {
        if (!$this->is_active) return 'secondary';
        if ($this->failed_forward_count > 0 && $this->failed_forward_count > $this->total_forwarded_count) {
            return 'danger';
        }
        return 'success';
    }

    public static function findMatchingRule(MpesaTransaction $transaction): ?self
    {
        return self::active()
            ->autoForward()
            ->get()
            ->first(fn($rule) => $rule->matchesTransaction($transaction));
    }
}
