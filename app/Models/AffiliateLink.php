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
        'short_code',
        'commission_rate',
        'status',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'total_clicks',
        'total_conversions',
        'conversion_rate',
        'total_commission',
        'click_commission',
        'combined_commission',
        'effective_commission_rate',
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
        // Tính hoa hồng từ transactions liên quan đến conversions của affiliate link này
        $conversionIds = $this->conversions()->pluck('id')->toArray();
        if (empty($conversionIds)) {
            return 0.0;
        }
        
        return $this->publisher->transactions()
            ->where('type', 'commission_earned')
            ->where('reference_type', 'conversion_commission')
            ->whereIn('reference_id', $conversionIds)
            ->sum('amount');
    }

    // New methods for CPC-based commission calculation
    public function getClickCommissionAttribute(): float
    {
        // Tính hoa hồng từ transactions liên quan đến affiliate link này
        $clickIds = $this->clicks()->pluck('id')->toArray();
        if (empty($clickIds)) {
            return 0.0;
        }
        
        return $this->publisher->transactions()
            ->where('type', 'commission_earned')
            ->where('reference_type', 'click_commission')
            ->whereIn('reference_id', $clickIds)
            ->sum('amount');
    }

    public function getCombinedCommissionAttribute(): float
    {
        return $this->getClickCommissionAttribute() + $this->getTotalCommissionAttribute();
    }

    public function getCostPerClickAttribute(): float
    {
        // If campaign exists, use its CPC, otherwise default to 100 VND
        if ($this->campaign && $this->campaign->cost_per_click) {
            return $this->campaign->cost_per_click;
        }
        return 100.00; // Default CPC
    }

    // Override commission_rate to get from campaign if available, otherwise use stored value
    public function getEffectiveCommissionRateAttribute(): float
    {
        // If campaign exists, use its commission rate (auto mode)
        if ($this->campaign && $this->campaign->commission_rate) {
            return $this->campaign->commission_rate;
        }
        
        // If no campaign, use stored commission rate (manual mode)
        return $this->commission_rate ?? 15.00;
    }

    // Check if this link is in auto mode (campaign-based)
    public function isAutoCommissionMode(): bool
    {
        return $this->campaign && $this->campaign->commission_rate;
    }

    // Check if this link is in manual mode (product-based)
    public function isManualCommissionMode(): bool
    {
        return !$this->isAutoCommissionMode();
    }

    // Allow setting commission_rate when there's no campaign
    public function setCommissionRateAttribute($value)
    {
        // Only allow setting commission_rate if there's no campaign
        if (!$this->campaign_id) {
            $this->attributes['commission_rate'] = $value;
        }
        // If there's a campaign, commission_rate will be automatically derived from campaign
    }
}
