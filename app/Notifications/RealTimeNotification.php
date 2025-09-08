<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class RealTimeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private array $data,
        private array $channels = ['database', 'broadcast']
    ) {}

    public function via($notifiable): array
    {
        return $this->channels;
    }

    public function toDatabase($notifiable): array
    {
        return $this->data;
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => $this->data['type'],
            'title' => $this->data['title'],
            'message' => $this->data['message'],
            'icon' => $this->data['icon'],
            'color' => $this->data['color'],
            'data' => $this->data['data'],
            'created_at' => $this->data['created_at'],
            'read_at' => null,
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            'user.' . $this->notifiable->id,
            'notifications',
        ];
    }
}
