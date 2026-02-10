<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'approved_at',
        'active',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function links()
    {
        return $this->hasMany(AffiliateLink::class);
    }

    public function commissions()
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function referrals()
    {
        return $this->hasManyThrough(AffiliateReferral::class, AffiliateLink::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(AffiliateWithdrawal::class);
    }

    /**
     * Attribute Helpers
     */

    /**
     * Get total earned commissions (approved or paid).
     */
    public function getTotalEarnedAttribute()
    {
        return round($this->commissions()
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount'), 2);
    }

    /**
     * Get total paid commissions.
     */
    public function getTotalPaidAttribute()
    {
        return round($this->commissions()
            ->where('status', 'paid')
            ->sum('amount'), 2);
    }

    /**
     * Get total clicks.
     */
    public function getTotalClicksAttribute()
    {
        return $this->links->flatMap->referrals->count();
    }

    /**
     * Get total referrals with user attached.
     */
    public function getTotalReferralsAttribute()
    {
        return $this->links->flatMap->referrals->whereNotNull('user_id')->count();
    }

    /**
     * Get total conversions (purchases).
     */
    public function getTotalConversionsAttribute()
    {
        return $this->links->flatMap->referrals->whereNotNull('purchased_at')->count();
    }

    /**
     * Get available earnings for withdrawal.
     * Only includes approved commissions with pending payment status (not yet paid)
     */
    public function getAvailableEarnings()
    {
        $totalEarned = $this->commissions()
            ->where('status', 'approved')
            ->where('payment_status', 'pending')
            ->sum('amount');

        $alreadyWithdrawn = $this->withdrawals()
            ->whereIn('status', ['pending', 'processing', 'paid'])
            ->sum('amount');

        // Always round to 2 decimals to avoid floating point issues
        return round(max($totalEarned - $alreadyWithdrawn, 0), 2);
    }

    /**
     * Model boot: automatically generate a unique code when creating an affiliate.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($affiliate) {
            if (empty($affiliate->code)) {
                do {
                    $code = Str::random(8);
                } while (self::where('code', $code)->exists());
                $affiliate->code = $code;
            }
        });
    }
}