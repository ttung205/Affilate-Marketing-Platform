<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalApproval extends Model
{
    protected $fillable = [
        'withdrawal_id',
        'admin_id',
        'action',
        'notes',
        'verification_data',
    ];

    protected $casts = [
        'verification_data' => 'array',
    ];

    // Relationships
    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(Withdrawal::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Helper methods
    public function isApproval(): bool
    {
        return $this->action === 'approve';
    }

    public function isRejection(): bool
    {
        return $this->action === 'reject';
    }

    public function isInfoRequest(): bool
    {
        return $this->action === 'request_info';
    }

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'approve' => 'Phê duyệt',
            'reject' => 'Từ chối',
            'request_info' => 'Yêu cầu thông tin',
            default => 'Không xác định'
        };
    }

    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'approve' => 'green',
            'reject' => 'red',
            'request_info' => 'yellow',
            default => 'gray'
        };
    }

    public function getIconAttribute(): string
    {
        return match($this->action) {
            'approve' => 'fas fa-check-circle text-green-500',
            'reject' => 'fas fa-times-circle text-red-500',
            'request_info' => 'fas fa-question-circle text-yellow-500',
            default => 'fas fa-circle text-gray-500'
        };
    }
}
