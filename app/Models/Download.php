<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Download extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'token',
        'file_format',
        'download_count',
        'max_downloads',
        'expires_at',
        'last_downloaded_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($download) {
            if (empty($download->token)) {
                $download->token = Str::random(64);
            }
            if (empty($download->max_downloads)) {
                $download->max_downloads = 3; // Default max downloads
            }
            if (empty($download->expires_at)) {
                $download->expires_at = now()->addDays(7); // Default 7 days expiry
            }
        });
    }

    /**
     * Get the user that owns the download.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that belongs to the download.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order that belongs to the download.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if download is valid.
     */
    public function isValid()
    {
        return $this->download_count < $this->max_downloads 
               && $this->expires_at > now();
    }

    /**
     * Increment download count.
     */
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
    }

    /**
     * Check if download is expired.
     */
    public function isExpired()
    {
        return $this->expires_at < now();
    }

    /**
     * Check if max downloads reached.
     */
    public function maxDownloadsReached()
    {
        return $this->download_count >= $this->max_downloads;
    }
}
