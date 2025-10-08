<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformFeeSetting extends Model
{
    protected $fillable = [
        'fee_percentage',
        'description',
        'effective_from',
        'is_active'
    ];

    protected $casts = [
        'effective_from' => 'datetime',
        'is_active' => 'boolean',
        'fee_percentage' => 'decimal:2'
    ];

    /**
     * Lấy phí sàn hiện tại đang áp dụng
     */
    public static function getCurrentFee()
    {
        return self::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', now());
            })
            ->orderBy('effective_from', 'desc')
            ->first();
    }
}
