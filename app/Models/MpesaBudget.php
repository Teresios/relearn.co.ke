<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MpesaBudget extends Model
{
    protected $fillable = [
        'name',
        'description',
        'period_type',
        'period_start',
        'period_end',
        'budget_limit',
        'warning_threshold',
        'critical_threshold',
        'spent_amount',
        'transaction_count',
        'transaction_types',
        'direction',
        'alerts_enabled',
        'alert_emails',
        'alert_phones',
        'alert_on_warning',
        'alert_on_critical',
        'alert_on_exceeded',
        'block_on_exceeded',
        'warning_alert_sent_at',
        'critical_alert_sent_at',
        'exceeded_alert_sent_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'budget_limit' => 'decimal:2',
        'warning_threshold' => 'decimal:2',
        'critical_threshold' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'transaction_types' => 'array',
        'alert_emails' => 'array',
        'alert_phones' => 'array',
        'alerts_enabled' => 'boolean',
        'alert_on_warning' => 'boolean',
        'alert_on_critical' => 'boolean',
        'alert_on_exceeded' => 'boolean',
        'block_on_exceeded' => 'boolean',
        'is_active' => 'boolean',
        'period_start' => 'date',
        'period_end' => 'date',
        'warning_alert_sent_at' => 'datetime',
        'critical_alert_sent_at' => 'datetime',
        'exceeded_alert_sent_at' => 'datetime',
    ];

    const PERIOD_DAILY = 'daily';
    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_CUSTOM = 'custom';

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

    public function scopeOfPeriod($query, string $period)
    {
        return $query->where('period_type', $period);
    }

    // Helper methods
    public function getUsedPercentage(): float
    {
        if ($this->budget_limit <= 0) return 0;
        return min(100, ($this->spent_amount / $this->budget_limit) * 100);
    }

    public function getRemainingAmount(): float
    {
        return max(0, $this->budget_limit - $this->spent_amount);
    }

    public function isWarningLevel(): bool
    {
        return $this->getUsedPercentage() >= $this->warning_threshold;
    }

    public function isCriticalLevel(): bool
    {
        return $this->getUsedPercentage() >= $this->critical_threshold;
    }

    public function isExceeded(): bool
    {
        return $this->spent_amount >= $this->budget_limit;
    }

    public function getStatus(): string
    {
        if ($this->isExceeded()) return 'exceeded';
        if ($this->isCriticalLevel()) return 'critical';
        if ($this->isWarningLevel()) return 'warning';
        return 'normal';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->getStatus()) {
            'exceeded' => 'danger',
            'critical' => 'danger',
            'warning' => 'warning',
            default => 'success',
        };
    }

    public function getCurrentPeriodDates(): array
    {
        $now = Carbon::now();
        
        return match($this->period_type) {
            self::PERIOD_DAILY => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            self::PERIOD_WEEKLY => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
            self::PERIOD_MONTHLY => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            self::PERIOD_CUSTOM => [
                'start' => $this->period_start,
                'end' => $this->period_end,
            ],
            default => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
        };
    }

    public function recalculateSpending(): void
    {
        $period = $this->getCurrentPeriodDates();
        
        $query = MpesaTransaction::successful()
            ->whereBetween('created_at', [$period['start'], $period['end']]);
        
        // Apply direction filter
        if ($this->direction) {
            $query->where('direction', $this->direction);
        }
        
        // Apply transaction type filter
        if (!empty($this->transaction_types)) {
            $query->whereIn('type', $this->transaction_types);
        }
        
        $this->spent_amount = $query->sum('amount');
        $this->transaction_count = $query->count();
        $this->save();
    }

    public function shouldBlockTransaction(): bool
    {
        return $this->is_active && $this->block_on_exceeded && $this->isExceeded();
    }

    public function canProcessTransaction(float $amount): bool
    {
        if (!$this->is_active) return true;
        if (!$this->block_on_exceeded) return true;
        
        return ($this->spent_amount + $amount) <= $this->budget_limit;
    }

    public function addSpending(float $amount): void
    {
        $this->increment('spent_amount', $amount);
        $this->increment('transaction_count');
        
        // Check for alerts
        $this->checkAndSendAlerts();
    }

    protected function checkAndSendAlerts(): void
    {
        if (!$this->alerts_enabled) return;
        
        $percentage = $this->getUsedPercentage();
        
        // Exceeded alert
        if ($this->alert_on_exceeded && $this->isExceeded() && !$this->exceeded_alert_sent_at) {
            $this->sendBudgetAlert('exceeded');
            $this->update(['exceeded_alert_sent_at' => now()]);
        }
        // Critical alert
        elseif ($this->alert_on_critical && $this->isCriticalLevel() && !$this->critical_alert_sent_at) {
            $this->sendBudgetAlert('critical');
            $this->update(['critical_alert_sent_at' => now()]);
        }
        // Warning alert
        elseif ($this->alert_on_warning && $this->isWarningLevel() && !$this->warning_alert_sent_at) {
            $this->sendBudgetAlert('warning');
            $this->update(['warning_alert_sent_at' => now()]);
        }
    }

    protected function sendBudgetAlert(string $level): void
    {
        // Dispatch budget alert event/notification
        event(new \App\Events\MpesaBudgetAlert($this, $level));
    }

    public function resetForNewPeriod(): void
    {
        $this->update([
            'spent_amount' => 0,
            'transaction_count' => 0,
            'warning_alert_sent_at' => null,
            'critical_alert_sent_at' => null,
            'exceeded_alert_sent_at' => null,
        ]);
    }
}
