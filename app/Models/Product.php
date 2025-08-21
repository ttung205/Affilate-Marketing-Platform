<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'category_id',
        'stock',
        'is_active',
        'affiliate_link',
        'commission_rate',
        'user_id'
    ];

    protected $casts = [
        'price' => 'decimal:2', // Lưu dưới dạng decimal với 2 chữ số thập phân
        'is_active' => 'boolean',
        'commission_rate' => 'decimal:2'
    ];

    // Accessor để format giá tiền khi hiển thị
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2, ',', '.') . ' VND';
    }

    // Mutator để xử lý giá tiền khi lưu
    public function setPriceAttribute($value)
    {
        // Loại bỏ tất cả ký tự không phải số và dấu chấm thập phân
        $cleanValue = preg_replace('/[^0-9.]/', '', $value);
        $this->attributes['price'] = (float) $cleanValue;
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Shop owner relationship
    public function shopOwner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Affiliate Relationships
    public function affiliateLinks(): HasMany
    {
        return $this->hasMany(AffiliateLink::class, 'product_id');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class, 'product_id');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(Conversion::class, 'product_id');
    }

    // Helper methods for affiliate
    public function getTotalClicksAttribute(): int
    {
        return $this->clicks()->count();
    }

    public function getTotalConversionsAttribute(): int
    {
        return $this->conversions()->count();
    }

    public function getTotalCommissionAttribute(): float
    {
        return $this->conversions()->sum('commission');
    }

    public function getConversionRateAttribute(): float
    {
        $clicks = $this->getTotalClicksAttribute();
        if ($clicks === 0) return 0;
        
        return round(($this->getTotalConversionsAttribute() / $clicks) * 100, 2);
    }

    public function getAffiliateCommissionAttribute(): float
    {
        return $this->commission_rate ?? 15.00; // Mặc định 15%
    }
}