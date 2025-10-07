<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublisherRanking extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'level',
        'color',
        'min_links',
        'min_commission',
        'bonus_percentage',
        'benefits',
        'description',
        'is_active',
    ];

    protected $casts = [
        'min_commission' => 'decimal:2',
        'bonus_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function publishers(): HasMany
    {
        return $this->hasMany(User::class, 'publisher_ranking_id');
    }

    // Helper methods
    public function getFormattedMinCommissionAttribute(): string
    {
        return number_format($this->min_commission, 0, ',', '.') . ' VND';
    }

    public function getBonusTextAttribute(): string
    {
        if ($this->bonus_percentage > 0) {
            return "+{$this->bonus_percentage}% bonus";
        }
        return 'Không có bonus';
    }

    public function getIconAttribute(): string
    {
        return match ($this->slug) {
            'dong' => '🥉',
            'bac' => '🥈',
            'vang' => '🥇',
            'kim-cuong' => '💎',
            default => '⭐'
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('level', 'asc');
    }

    // Static methods
    public static function getByLevel(int $level): ?self
    {
        return static::where('level', $level)->active()->first();
    }

    public static function getNextRanking(PublisherRanking $current): ?self
    {
        return static::where('level', '>', $current->level)
            ->active()
            ->orderBy('level')
            ->first();
    }
}
