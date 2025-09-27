<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $withdrawal;
    protected $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(Withdrawal $withdrawal, string $status)
    {
        $this->withdrawal = $withdrawal;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->getStatusSubject();
        $message = $this->getStatusMessage();
        
        $mailMessage = (new MailMessage)
            ->subject($subject . ' - ' . config('app.name'))
            ->greeting('Xin chào ' . $notifiable->name . '!')
            ->line($message)
            ->line('Số tiền: ' . number_format((float)$this->withdrawal->amount, 0, ',', '.') . ' VNĐ')
            ->line('Phương thức: ' . $this->withdrawal->payment_method_label);

        if ($this->status === 'completed') {
            $mailMessage->line('Số tiền thực nhận: ' . number_format((float)$this->withdrawal->net_amount, 0, ',', '.') . ' VNĐ');
            if ($this->withdrawal->transaction_reference) {
                $mailMessage->line('Mã giao dịch: ' . $this->withdrawal->transaction_reference);
            }
        }

        if ($this->status === 'rejected' && $this->withdrawal->rejection_reason) {
            $mailMessage->line('Lý do từ chối: ' . $this->withdrawal->rejection_reason);
        }

        $mailMessage->action('Xem chi tiết', route('publisher.withdrawal.show', $this->withdrawal));

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'withdrawal_status',
            'title' => $this->getStatusTitle(),
            'message' => $this->getStatusMessage(),
            'icon' => $this->getStatusIcon(),
            'color' => $this->getStatusColor(),
            'data' => [
                'withdrawal_id' => $this->withdrawal->id,
                'status' => $this->status,
                'amount' => $this->withdrawal->amount,
                'net_amount' => $this->withdrawal->net_amount,
                'payment_method' => $this->withdrawal->payment_method_label,
                'rejection_reason' => $this->withdrawal->rejection_reason,
                'transaction_reference' => $this->withdrawal->transaction_reference,
                'processed_at' => $this->withdrawal->processed_at?->toISOString(),
                'created_at' => $this->withdrawal->created_at->toISOString(),
            ],
            'created_at' => now()->toISOString(),
        ];
    }

    private function getStatusSubject(): string
    {
        return match($this->status) {
            'approved' => 'Yêu cầu rút tiền đã được phê duyệt',
            'rejected' => 'Yêu cầu rút tiền bị từ chối',
            'completed' => 'Rút tiền đã hoàn thành',
            'cancelled' => 'Yêu cầu rút tiền đã bị hủy',
            default => 'Cập nhật trạng thái rút tiền'
        };
    }

    private function getStatusTitle(): string
    {
        return match($this->status) {
            'approved' => 'Rút tiền đã được phê duyệt',
            'rejected' => 'Rút tiền bị từ chối',
            'completed' => 'Rút tiền hoàn thành',
            'cancelled' => 'Rút tiền đã hủy',
            default => 'Cập nhật rút tiền'
        };
    }

    private function getStatusMessage(): string
    {
        return match($this->status) {
            'approved' => 'Yêu cầu rút tiền của bạn đã được phê duyệt và đang được xử lý',
            'rejected' => 'Yêu cầu rút tiền của bạn đã bị từ chối',
            'completed' => 'Yêu cầu rút tiền của bạn đã được hoàn thành',
            'cancelled' => 'Yêu cầu rút tiền của bạn đã bị hủy',
            default => 'Trạng thái rút tiền đã được cập nhật'
        };
    }

    private function getStatusIcon(): string
    {
        return match($this->status) {
            'approved' => 'fas fa-check-circle',
            'rejected' => 'fas fa-times-circle',
            'completed' => 'fas fa-check-double',
            'cancelled' => 'fas fa-ban',
            default => 'fas fa-info-circle'
        };
    }

    private function getStatusColor(): string
    {
        return match($this->status) {
            'approved' => 'blue',
            'rejected' => 'red',
            'completed' => 'green',
            'cancelled' => 'gray',
            default => 'yellow'
        };
    }
}
