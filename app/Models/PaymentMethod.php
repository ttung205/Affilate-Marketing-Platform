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
    ];

    protected $casts = [
        'is_default' => 'boolean',
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
            'bank_transfer' => 'Tài khoản ngân hàng',
            default => 'Tài khoản ngân hàng'
        };
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->account_name} - {$this->bank_name} ({$this->account_number})";
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
        // Chuyển khoản ngân hàng miễn phí
        return 0.0;
    }

    public function calculateFee(float $amount): float
    {
        return $amount * $this->fee_rate;
    }

    public function getIconAttribute(): string
    {
        return 'fas fa-university';
    }

    public function getColorAttribute(): string
    {
        return 'blue';
    }
}
