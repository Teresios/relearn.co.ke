<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AffiliateCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id', 'order_id', 'referral_id', 'amount', 'status', 'paid_at', 'payment_status', 'payment_processed_at'
    ];

    protected $dates = [
        'paid_at',
        'payment_processed_at',
    ];

    /**
     * Relationships
     */
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function referral()
    {
        return $this->belongsTo(AffiliateReferral::class, 'referral_id');
    }

    /**
     * Check if commission is pending payment
     */
    public function isPending(): bool
    {
        return $this->payment_status === 'pending' && $this->status === 'approved';
    }

    /**
     * Check if commission has been paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid' && $this->status === 'paid';
    }

    /**
     * Mark commission as paid
     */
    public function markAsPaid(): void
    {
        $this->update([
            'payment_status' => 'paid',
            'status' => 'paid',
            'payment_processed_at' => now(),
            'paid_at' => now(),
        ]);
    }

    /**
     * Scope: Get all pending payments
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending')->where('status', 'approved');
    }

    /**
     * Scope: Get all paid payments
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid')->where('status', 'paid');
    }
}