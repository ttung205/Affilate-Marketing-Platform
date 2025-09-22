<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Withdrawal extends Model
{
    protected $fillable = [
        'publisher_id',
        'payment_method_id',
        'amount',
        'fee',
        'net_amount',
        'status',
        'payment_method_type',
        'payment_details',
        'admin_notes',
        'rejection_reason',
        'processed_by',
        'processed_at',
        'completed_at',
        'transaction_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payment_details' => 'array',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(WithdrawalApproval::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'reference_id')
            ->where('reference_type', 'withdrawal');
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'approved']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'rejected' => 'Từ chối',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'approved' => 'blue',
            'processing' => 'indigo',
            'completed' => 'green',
            'rejected' => 'red',
            'cancelled' => 'gray',
            default => 'gray'
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method_type) {
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'momo' => 'Ví MoMo',
            'zalopay' => 'Ví ZaloPay',
            'vnpay' => 'Ví VNPay',
            'phone_card' => 'Thẻ cào điện thoại',
            default => 'Không xác định'
        };
    }
}
