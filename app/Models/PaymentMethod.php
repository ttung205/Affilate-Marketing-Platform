<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $fillable = [
        'publisher_id',
        'type',
        'account_name',
        'account_number',
        'bank_name',
        'bank_code',
        'branch_name',
        'is_default',
        'is_verified',
        'verified_at',
        'verification_data',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'verification_data' => 'array',
    ];

    // Relationships
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    // Helper methods
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'momo' => 'Ví MoMo',
            'zalopay' => 'Ví ZaloPay',
            'vnpay' => 'Ví VNPay',
            'phone_card' => 'Thẻ cào điện thoại',
            default => 'Không xác định'
        };
    }

    public function getDisplayNameAttribute(): string
    {
        return match($this->type) {
            'bank_transfer' => "{$this->account_name} - {$this->bank_name} ({$this->account_number})",
            'momo', 'zalopay', 'vnpay' => "{$this->account_name} - {$this->account_number}",
            'phone_card' => "Thẻ cào - {$this->account_number}",
            default => $this->account_name
        };
    }

    public function getMaskedAccountNumberAttribute(): string
    {
        if (strlen($this->account_number) <= 4) {
            return str_repeat('*', strlen($this->account_number));
        }
        
        return substr($this->account_number, 0, 2) . str_repeat('*', strlen($this->account_number) - 4) . substr($this->account_number, -2);
    }

    public function getFeeRateAttribute(): float
    {
        return match($this->type) {
            'bank_transfer' => 0.0, // Miễn phí
            'momo', 'zalopay', 'vnpay' => 0.01, // 1%
            'phone_card' => 0.05, // 5%
            default => 0.0
        };
    }

    public function calculateFee(float $amount): float
    {
        return $amount * $this->fee_rate;
    }

    public function getIconAttribute(): string
    {
        return match($this->type) {
            'bank_transfer' => 'fas fa-university',
            'momo' => 'fab fa-momo',
            'zalopay' => 'fas fa-mobile-alt',
            'vnpay' => 'fas fa-credit-card',
            'phone_card' => 'fas fa-sim-card',
            default => 'fas fa-credit-card'
        };
    }

    public function getColorAttribute(): string
    {
        return match($this->type) {
            'bank_transfer' => 'blue',
            'momo' => 'pink',
            'zalopay' => 'blue',
            'vnpay' => 'green',
            'phone_card' => 'orange',
            default => 'gray'
        };
    }
}
