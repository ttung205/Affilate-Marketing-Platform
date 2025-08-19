<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    protected $fillable = [
        'affiliate_link_id',
        'publisher_id',
        'product_id',
        'tracking_code',
        'ip_address',
        'user_agent',
        'referrer',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
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
    public function getIsUniqueClickAttribute(): bool
    {
        // Kiểm tra xem IP này đã click link này chưa
        return !$this->affiliateLink->clicks()
            ->where('ip_address', $this->ip_address)
            ->where('id', '!=', $this->id)
            ->exists();
    }
}
