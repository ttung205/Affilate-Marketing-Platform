<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function isPublisher(): bool
    {
        return in_array($this->role, ['shop', 'publisher']);
    }

    public function isShop(): bool
    {
        return $this->role === 'shop';
    }
}
