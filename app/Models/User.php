<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\HasDatabaseNotifications;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'google_id',
        'avatar',
        'phone',
        'address',
        'bio',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Affiliate Relationships
    public function affiliateLinks(): HasMany
    {
        return $this->hasMany(AffiliateLink::class, 'publisher_id');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class, 'publisher_id');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(Conversion::class, 'publisher_id');
    }

    // Helper methods for affiliate
    public function getTotalClicksAttribute(): int
    {
        return $this->clicks()->count();
    }

    public function getTotalConversionsAttribute(): int
    {
        return $this->conversions()->count();
    }

    public function getTotalCommissionAttribute(): float
    {
        return $this->conversions()->sum('commission');
    }

    public function getConversionRateAttribute(): float
    {
        $clicks = $this->getTotalClicksAttribute();
        if ($clicks === 0) return 0;
        
        return round(($this->getTotalConversionsAttribute() / $clicks) * 100, 2);
    }

    // New methods for CPC-based commission calculation
    public function getClickCommissionAttribute(): float
    {
        $totalClicks = $this->getTotalClicksAttribute();
        $cpc = $this->getDefaultCostPerClickAttribute();
        return $totalClicks * $cpc;
    }

    public function getCombinedCommissionAttribute(): float
    {
        return $this->getClickCommissionAttribute() + $this->getTotalCommissionAttribute();
    }

    public function getDefaultCostPerClickAttribute(): float
    {
        // Check if any affiliate links have campaigns with CPC
        $campaignCpc = $this->affiliateLinks()
            ->whereHas('campaign', function($query) {
                $query->whereNotNull('cost_per_click');
            })
            ->join('campaigns', 'affiliate_links.campaign_id', '=', 'campaigns.id')
            ->avg('campaigns.cost_per_click');
        
        return $campaignCpc ?? 100.00; // Default 100 VND if no campaign CPC
    }

    public function isPublisher(): bool
    {
        return in_array($this->role, ['shop', 'publisher']);
    }

    public function isShop(): bool
    {
        return $this->role === 'shop';
    }

    // Wallet relationships
    public function wallet()
    {
        return $this->hasOne(PublisherWallet::class, 'publisher_id');
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class, 'publisher_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'publisher_id');
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class, 'publisher_id');
    }

    public function defaultPaymentMethod()
    {
        return $this->hasOne(PaymentMethod::class, 'publisher_id')->where('is_default', true);
    }

    // Wallet helper methods
    public function getOrCreateWallet(): PublisherWallet
    {
        return $this->wallet ?: $this->wallet()->create([
            'balance' => 0,
            'pending_balance' => 0,
            'total_earned' => 0,
            'total_withdrawn' => 0,
            'hold_period_days' => 30,
            'is_active' => true,
        ]);
    }

    public function getAvailableBalance(): float
    {
        return $this->getOrCreateWallet()->balance;
    }

    public function getTotalBalance(): float
    {
        return $this->getOrCreateWallet()->total_balance;
    }

    public function canWithdraw(float $amount): bool
    {
        return $this->getOrCreateWallet()->canWithdraw($amount);
    }
}
