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
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
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
}
