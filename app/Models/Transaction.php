<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    protected $fillable = [
        'publisher_id',
        'type',
        'amount',
        'status',
        'description',
        'reference_type',
        'reference_id',
        'metadata',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    // Relationships
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo('reference');
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'commission_earned' => 'Hoa hồng kiếm được',
            'withdrawal' => 'Rút tiền',
            'refund' => 'Hoàn tiền',
            'bonus' => 'Thưởng',
            'penalty' => 'Phạt',
            'adjustment' => 'Điều chỉnh',
            default => 'Không xác định'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Chờ xử lý',
            'completed' => 'Hoàn thành',
            'failed' => 'Thất bại',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'completed' => 'green',
            'failed' => 'red',
            'cancelled' => 'gray',
            default => 'gray'
        };
    }

    public function getAmountFormattedAttribute(): string
    {
        $prefix = in_array($this->type, ['commission_earned', 'refund', 'bonus']) ? '+' : '-';
        return $prefix . number_format($this->amount, 0, ',', '.') . ' VNĐ';
    }

    public function getIconAttribute(): string
    {
        return match($this->type) {
            'commission_earned' => 'fas fa-plus-circle text-green-500',
            'withdrawal' => 'fas fa-minus-circle text-red-500',
            'refund' => 'fas fa-undo text-blue-500',
            'bonus' => 'fas fa-gift text-purple-500',
            'penalty' => 'fas fa-exclamation-triangle text-orange-500',
            'adjustment' => 'fas fa-edit text-gray-500',
            default => 'fas fa-circle text-gray-500'
        };
    }
}
