<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversion extends Model
{
    protected $fillable = [
        'affiliate_link_id',
        'publisher_id',
        'product_id',
        'tracking_code',
        'order_id',
        'amount',
        'commission',
        'converted_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'converted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function affiliateLink(): BelongsTo
    {
        return $this->belongsTo(AffiliateLink::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Helper methods
    public function getCommissionRateAttribute(): float
    {
        if ($this->amount == 0) return 0;
        return round(($this->commission / $this->amount) * 100, 2);
    }

    public function getRoiAttribute(): float
    {
        // Return on Investment - Lợi nhuận đầu tư
        return $this->commission;
    }
}
