<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'amount',
        'status',
        'mpesa_checkout_request_id',
        'mpesa_receipt_number',
        'payment_phone',
        'customer_email',
        'transaction_date',
        'download_token',
        'thank_you_sent',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'thank_you_sent' => 'boolean',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that belongs to the order.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the payment for the order.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get the downloads for the order.
     */
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    /**
     * Check if order is completed.
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Mark order as completed.
     */
    public function markAsCompleted()
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Get formatted amount attribute.
     */
    public function getFormattedAmountAttribute()
    {
        return 'KES ' . number_format($this->amount, 2);
    }

    protected static function booted()
    {
        static::saving(function ($order) {
            if (
                $order->isDirty('status') && 
                $order->status === 'completed' && 
                empty($order->download_token)
            )  {
            $order->download_token = Str::random(32);
        }
    });
    }
}
