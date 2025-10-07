<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RealTimeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     * Simplified to only use database channel with polling.
     */
    public function __construct(
        private array $data
    ) {}

    /**
     * Get the notification's delivery channels.
     * Only using database channel since we're using polling for real-time updates.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toDatabase($notifiable): array
    {
        return $this->data;
    }
}
