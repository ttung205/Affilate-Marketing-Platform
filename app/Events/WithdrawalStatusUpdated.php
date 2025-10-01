<?php

namespace App\Events;

use App\Models\Withdrawal;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WithdrawalStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('withdrawal.' . $this->withdrawal->id),
            new PrivateChannel('user.' . $this->withdrawal->publisher_id),
            new PrivateChannel('admin.withdrawals'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'withdrawal.status.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
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
