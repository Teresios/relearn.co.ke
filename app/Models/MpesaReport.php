<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaReport extends Model
{
    protected $fillable = [
        'report_type',
        'period_start',
        'period_end',
        'total_inbound',
        'total_outbound',
        'net_amount',
        'c2b_count',
        'c2b_amount',
        'b2c_count',
        'b2c_amount',
        'b2b_count',
        'b2b_amount',
        'stk_push_count',
        'stk_push_amount',
        'reversal_count',
        'reversal_amount',
        'successful_transactions',
        'failed_transactions',
        'success_rate',
        'forwarded_count',
        'forwarded_amount',
        'detailed_breakdown',
        'hourly_breakdown',
        'top_senders',
        'top_receivers',
        'generated_at',
        'generated_by',
        'file_path',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_inbound' => 'decimal:2',
        'total_outbound' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'c2b_amount' => 'decimal:2',
        'b2c_amount' => 'decimal:2',
        'b2b_amount' => 'decimal:2',
        'stk_push_amount' => 'decimal:2',
        'reversal_amount' => 'decimal:2',
        'success_rate' => 'decimal:2',
        'forwarded_amount' => 'decimal:2',
        'detailed_breakdown' => 'array',
        'hourly_breakdown' => 'array',
        'top_senders' => 'array',
        'top_receivers' => 'array',
        'generated_at' => 'datetime',
    ];

    const TYPE_DAILY = 'daily';
    const TYPE_WEEKLY = 'weekly';
    const TYPE_MONTHLY = 'monthly';
    const TYPE_CUSTOM = 'custom';

    // Relationships
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // Scopes
    public function scopeOfType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('generated_at', '>=', now()->subDays($days));
    }

    // Static methods for generating reports
    public static function generateReport(string $type, $startDate = null, $endDate = null, $userId = null): self
    {
        $dates = self::calculatePeriodDates($type, $startDate, $endDate);
        
        $transactions = MpesaTransaction::whereBetween('created_at', [$dates['start'], $dates['end']]);
        
        // Calculate stats
        $c2bQuery = (clone $transactions)->where('type', MpesaTransaction::TYPE_C2B);
        $b2cQuery = (clone $transactions)->where('type', MpesaTransaction::TYPE_B2C);
        $b2bQuery = (clone $transactions)->where('type', MpesaTransaction::TYPE_B2B);
        $stkQuery = (clone $transactions)->where('type', MpesaTransaction::TYPE_STK_PUSH);
        $reversalQuery = (clone $transactions)->where('type', MpesaTransaction::TYPE_REVERSAL);
        
        $totalInbound = (clone $transactions)->inbound()->successful()->sum('amount');
        $totalOutbound = (clone $transactions)->outbound()->successful()->sum('amount');
        $successCount = (clone $transactions)->successful()->count();
        $failedCount = (clone $transactions)->failed()->count();
        $totalCount = $successCount + $failedCount;
        
        return self::updateOrCreate(
            [
                'report_type' => $type,
                'period_start' => $dates['start']->toDateString(),
                'period_end' => $dates['end']->toDateString(),
            ],
            [
                'total_inbound' => $totalInbound,
                'total_outbound' => $totalOutbound,
                'net_amount' => $totalInbound - $totalOutbound,
                
                'c2b_count' => (clone $c2bQuery)->count(),
                'c2b_amount' => (clone $c2bQuery)->successful()->sum('amount'),
                
                'b2c_count' => (clone $b2cQuery)->count(),
                'b2c_amount' => (clone $b2cQuery)->successful()->sum('amount'),
                
                'b2b_count' => (clone $b2bQuery)->count(),
                'b2b_amount' => (clone $b2bQuery)->successful()->sum('amount'),
                
                'stk_push_count' => (clone $stkQuery)->count(),
                'stk_push_amount' => (clone $stkQuery)->successful()->sum('amount'),
                
                'reversal_count' => (clone $reversalQuery)->count(),
                'reversal_amount' => (clone $reversalQuery)->successful()->sum('amount'),
                
                'successful_transactions' => $successCount,
                'failed_transactions' => $failedCount,
                'success_rate' => $totalCount > 0 ? ($successCount / $totalCount) * 100 : 100,
                
                'forwarded_count' => (clone $transactions)->where('is_forwarded', true)->count(),
                'forwarded_amount' => (clone $transactions)->where('is_forwarded', true)->sum('amount'),
                
                'hourly_breakdown' => self::getHourlyBreakdown($dates['start'], $dates['end']),
                'top_senders' => self::getTopSenders($dates['start'], $dates['end']),
                'top_receivers' => self::getTopReceivers($dates['start'], $dates['end']),
                
                'generated_at' => now(),
                'generated_by' => $userId,
            ]
        );
    }

    protected static function calculatePeriodDates(string $type, $start = null, $end = null): array
    {
        $now = now();
        
        return match($type) {
            self::TYPE_DAILY => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            self::TYPE_WEEKLY => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
            self::TYPE_MONTHLY => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            self::TYPE_CUSTOM => [
                'start' => $start ? \Carbon\Carbon::parse($start) : $now->copy()->startOfDay(),
                'end' => $end ? \Carbon\Carbon::parse($end) : $now->copy()->endOfDay(),
            ],
            default => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
        };
    }

    protected static function getHourlyBreakdown($start, $end): array
    {
        return MpesaTransaction::whereBetween('created_at', [$start, $end])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count, SUM(amount) as total')
            ->groupByRaw('HOUR(created_at)')
            ->orderBy('hour')
            ->get()
            ->mapWithKeys(fn($item) => [$item->hour => ['count' => $item->count, 'total' => $item->total]])
            ->toArray();
    }

    protected static function getTopSenders($start, $end, int $limit = 10): array
    {
        return MpesaTransaction::whereBetween('created_at', [$start, $end])
            ->whereNotNull('sender_phone')
            ->selectRaw('sender_phone, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('sender_phone')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    protected static function getTopReceivers($start, $end, int $limit = 10): array
    {
        return MpesaTransaction::whereBetween('created_at', [$start, $end])
            ->whereNotNull('receiver_phone')
            ->selectRaw('receiver_phone, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('receiver_phone')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
