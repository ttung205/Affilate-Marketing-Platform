<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'budget',
        'target_conversions',
        'commission_rate',
        'cost_per_click',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'cost_per_click' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function affiliateLinks(): HasMany
    {
        return $this->hasMany(AffiliateLink::class);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }

    public function getConversionRateAttribute(): float
    {
        $totalClicks = $this->getTotalClicksAttribute();
        if ($totalClicks === 0) {
            return 0.0;
        }
        
        return round(($this->getTotalConversionsAttribute() / $totalClicks) * 100, 2);
    }

    public function getTotalClicksAttribute(): int
    {
        return $this->affiliateLinks()
            ->join('clicks', 'affiliate_links.id', '=', 'clicks.affiliate_link_id')
            ->count();
    }

    public function getTotalConversionsAttribute(): int
    {
        return $this->affiliateLinks()
            ->join('conversions', 'affiliate_links.id', '=', 'conversions.affiliate_link_id')
            ->count();
    }

    public function getTotalCommissionAttribute(): float
    {
        return $this->affiliateLinks()
            ->join('conversions', 'affiliate_links.id', '=', 'conversions.affiliate_link_id')
            ->sum('conversions.commission');
    }

    // New methods for CPC-based commission calculation
    public function getClickCommissionAttribute(): float
    {
        $totalClicks = $this->getTotalClicksAttribute();
        $cpc = $this->cost_per_click ?? 100.00; // Default 100 VND if not set
        return $totalClicks * $cpc;
    }

    public function getConversionCommissionAttribute(): float
    {
        return $this->getTotalCommissionAttribute();
    }

    public function getCombinedCommissionAttribute(): float
    {
        return $this->getClickCommissionAttribute() + $this->getConversionCommissionAttribute();
    }

    public function getDefaultCostPerClickAttribute(): float
    {
        return $this->cost_per_click ?? 100.00;
    }
}
