<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'icon',
        'color',
        'channels',
        'is_active',
    ];

    protected $casts = [
        'channels' => 'array',
        'is_active' => 'boolean',
    ];

    // Helper methods
    public function getFormattedMessage(array $data = []): string
    {
        $message = $this->message;
        
        foreach ($data as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }
        
        return $message;
    }

    public function getFormattedTitle(array $data = []): string
    {
        $title = $this->title;
        
        foreach ($data as $key => $value) {
            $title = str_replace("{{$key}}", $value, $title);
        }
        
        return $title;
    }
}
