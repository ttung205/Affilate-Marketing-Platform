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
        'status',
        'status_changed_by',
        'status_changed_at',
        'status_note',
        'is_commission_processed',
        'commission_processed_at',
        'shop_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'converted_at' => 'datetime',
        'status_changed_at' => 'datetime',
        'commission_processed_at' => 'datetime',
        'is_commission_processed' => 'boolean',
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

    public function shop(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shop_id');
    }

    public function statusChangedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_changed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canProcessCommission(): bool
    {
        return $this->isApproved() && !$this->is_commission_processed;
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
