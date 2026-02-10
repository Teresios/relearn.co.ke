<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'sale_price',
        'show_discount',
        'image',
        'file_path',
        'file_name',
        'file_size',
        'pdf_file_path',
        'pdf_file_name',
        'pdf_file_size',
        'zip_file_path',
        'zip_file_name',
        'zip_file_size',
        'category',
        'is_active',
        'preview_url',
        'tags',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'file_size' => 'integer',
        'is_active' => 'boolean',
        'show_discount' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * Get the user who created the product.
     */
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Get the user who last updated the product.
     */
    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * Get the orders for the product.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the downloads for the product.
     */
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return 'KES ' . number_format($this->price, 2);
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes > 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the image URL for the product.
     */
    public function getImageUrl()
    {
        return $this->image ? asset('storage/products/images/' . $this->image) : null;
    }

    /**
     * Check if PDF version exists.
     */
    public function hasPdf()
    {
        return !empty($this->pdf_file_path);
    }

    /**
     * Get the formatted PDF file size.
     */
    public function getFormattedPdfFileSizeAttribute()
    {
        if (!$this->pdf_file_size) {
            return null;
        }
        
        $bytes = $this->pdf_file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes > 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the formatted ZIP file size.
     */
    public function getFormattedZipFileSizeAttribute()
    {
        if (!$this->zip_file_size) {
            return null;
        }
        
        $bytes = $this->zip_file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes > 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if ZIP version exists.
     */
    public function hasZip()
    {
        return !empty($this->zip_file_path);
    }

    /**
     * Get the effective price (sale price if available, otherwise regular price)
     */
    public function getEffectivePrice()
    {
        return $this->show_discount && $this->sale_price ? $this->sale_price : $this->price;
    }

    /**
     * Check if product has a discount
     */
    public function hasDiscount()
    {
        return $this->show_discount && $this->sale_price && $this->sale_price < $this->price;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentage()
    {
        if (!$this->hasDiscount()) {
            return 0;
        }
        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    /**
     * Get formatted original price (struck through if on sale)
     */
    public function getFormattedOriginalPrice()
    {
        if ($this->hasDiscount()) {
            return '<span style="text-decoration: line-through; color: #999;">KES ' . number_format($this->price, 2) . '</span>';
        }
        return 'KES ' . number_format($this->price, 2);
    }

    /**
     * Get formatted sale price
     */
    public function getFormattedSalePrice()
    {
        return 'KES ' . number_format($this->getEffectivePrice(), 2);
    }
}
