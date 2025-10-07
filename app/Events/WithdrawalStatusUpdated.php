<?php

namespace App\Events;

use App\Models\Withdrawal;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when withdrawal status is updated
 * Broadcasting disabled - using polling-based notifications instead
 */
class WithdrawalStatusUpdated
{
    use Dispatchable, SerializesModels;

    public $withdrawal;
    public $previousStatus;
    public $newStatus;
    public $updatedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Withdrawal $withdrawal, string $previousStatus, string $newStatus, $updatedBy = null)
    {
        $this->withdrawal = $withdrawal->load(['publisher', 'paymentMethod', 'processedBy']);
        $this->previousStatus = $previousStatus;
        $this->newStatus = $newStatus;
        $this->updatedBy = $updatedBy;
    }

    /**
     * Get withdrawal data for notification
     */
    public function getWithdrawalData(): array
    {
        return [
            'withdrawal' => [
                'id' => $this->withdrawal->id,
                'status' => $this->newStatus,
                'previous_status' => $this->previousStatus,
                'amount' => $this->withdrawal->amount,
                'fee' => $this->withdrawal->fee,
                'net_amount' => $this->withdrawal->net_amount,
                'publisher' => [
                    'id' => $this->withdrawal->publisher->id,
                    'name' => $this->withdrawal->publisher->name,
                    'email' => $this->withdrawal->publisher->email,
                ],
                'payment_method' => [
                    'type' => $this->withdrawal->payment_method_type,
                    'type_label' => $this->withdrawal->paymentMethod->type_label ?? 'N/A',
                ],
                'processed_at' => $this->withdrawal->processed_at?->toISOString(),
                'completed_at' => $this->withdrawal->completed_at?->toISOString(),
                'rejection_reason' => $this->withdrawal->rejection_reason,
                'transaction_reference' => $this->withdrawal->transaction_reference,
            ],
            'updated_by' => $this->updatedBy ? [
                'id' => $this->updatedBy->id,
                'name' => $this->updatedBy->name,
                'role' => $this->updatedBy->role,
            ] : null,
            'timestamp' => now()->toISOString(),
        ];
    }
}
