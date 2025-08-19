<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateLink extends Model
{
    protected $fillable = [
        'publisher_id',
        'product_id',
        'campaign_id',
        'original_url',
        'tracking_code',
        'commission_rate',
        'status',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(Conversion::class);
    }

    // Helper methods
    public function getFullUrlAttribute(): string
    {
        return url("/redirect/{$this->tracking_code}");
    }

    public function getTotalClicksAttribute(): int
    {
        return $this->clicks()->count();
    }

    public function getTotalConversionsAttribute(): int
    {
        return $this->conversions()->count();
    }

    public function getConversionRateAttribute(): float
    {
        $clicks = $this->getTotalClicksAttribute();
        if ($clicks === 0) return 0;
        
        return round(($this->getTotalConversionsAttribute() / $clicks) * 100, 2);
    }

    public function getTotalCommissionAttribute(): float
    {
        return $this->conversions()->sum('commission');
    }
}
