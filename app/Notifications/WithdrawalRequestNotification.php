<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRequestNotification extends Notification // implements ShouldQueue
{
    use Queueable;

    protected $withdrawal;

    /**
     * Create a new notification instance.
     */
    public function __construct(Withdrawal $withdrawal)
    {
        $this->withdrawal = $withdrawal;
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
        return (new MailMessage)
            ->subject('Yêu cầu rút tiền mới - ' . config('app.name'))
            ->greeting('Xin chào Admin!')
            ->line('Có yêu cầu rút tiền mới từ publisher: ' . $this->withdrawal->publisher->name)
            ->line('Số tiền: ' . number_format((float)$this->withdrawal->amount, 0, ',', '.') . ' VNĐ')
            ->line('Phương thức: ' . $this->withdrawal->payment_method_label)
            ->line('Thời gian: ' . $this->withdrawal->created_at->format('d/m/Y H:i'))
            ->action('Xem chi tiết', route('admin.withdrawals.show', $this->withdrawal))
            ->line('Vui lòng xem xét và xử lý yêu cầu này.');
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'withdrawal_request',
            'title' => 'Yêu cầu rút tiền mới',
            'message' => 'Publisher ' . $this->withdrawal->publisher->name . ' đã gửi yêu cầu rút tiền ' . number_format((float)$this->withdrawal->amount, 0, ',', '.') . ' VNĐ',
            'icon' => 'fas fa-money-bill-wave',
            'color' => 'blue',
            'data' => [
                'withdrawal_id' => $this->withdrawal->id,
                'publisher_name' => $this->withdrawal->publisher->name,
                'amount' => $this->withdrawal->amount,
                'payment_method' => $this->withdrawal->payment_method_label,
                'created_at' => $this->withdrawal->created_at->toISOString(),
            ],
            'created_at' => now()->toISOString(),
        ];
    }
}
