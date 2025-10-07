<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformFeePayment extends Model
{
    protected $fillable = [
        'shop_id',
        'total_products_value',
        'fee_percentage',
        'fee_amount',
        'status',
        'qr_code',
        'paid_at',
        'note',
        'admin_note',
        'verified_by',
        'verified_at'
    ];

    protected $casts = [
        'total_products_value' => 'decimal:2',
        'fee_percentage' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime'
    ];

    /**
     * Relationship với Shop
     */
    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_id');
    }

    /**
     * Relationship với Admin (người duyệt)
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
