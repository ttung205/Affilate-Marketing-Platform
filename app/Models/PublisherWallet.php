<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublisherWallet extends Model
{
    protected $fillable = [
        'publisher_id',
        'balance',
        'pending_balance',
        'total_earned',
        'total_withdrawn',
        'hold_period_days',
        'is_active',
        'last_withdrawal_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'hold_period_days' => 'integer',
        'is_active' => 'boolean',
        'last_withdrawal_at' => 'datetime',
    ];

    // Relationships
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class, 'publisher_id', 'publisher_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'publisher_id', 'publisher_id');
    }

    // Helper methods
    public function getAvailableBalanceAttribute(): float
    {
        return $this->balance;
    }

    public function getTotalBalanceAttribute(): float
    {
        return $this->balance + $this->pending_balance;
    }

    public function canWithdraw(float $amount): bool
    {
        return $this->is_active && $this->balance >= $amount;
    }

    public function addEarnings(float $amount): void
    {
        $this->increment('total_earned', $amount);
        $this->increment('pending_balance', $amount);
    }

    public function moveToAvailableBalance(float $amount): void
    {
        $this->decrement('pending_balance', $amount);
        $this->increment('balance', $amount);
    }

    public function deductBalance(float $amount): void
    {
        $this->decrement('balance', $amount);
        $this->increment('total_withdrawn', $amount);
        $this->update(['last_withdrawal_at' => now()]);
    }

    public function getWithdrawalLimitAttribute(): float
    {
        // Daily limit: 5,000,000 VNĐ
        $dailyLimit = 5000000;
        
        // Check if last withdrawal was today
        if ($this->last_withdrawal_at && $this->last_withdrawal_at->isToday()) {
            $todayWithdrawals = $this->withdrawals()
                ->whereDate('created_at', today())
                ->whereIn('status', ['completed', 'processing'])
                ->sum('amount');
            
            return max(0, $dailyLimit - $todayWithdrawals);
        }
        
        return $dailyLimit;
    }
}
