<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'type',
        'message',
        'ip_address',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the chat message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for user messages
     */
    public function scopeUserMessages($query)
    {
        return $query->where('type', 'user');
    }

    /**
     * Scope for bot messages
     */
    public function scopeBotMessages($query)
    {
        return $query->where('type', 'bot');
    }

    /**
     * Scope for specific session
     */
    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Get conversation history for a session
     */
    public static function getConversationHistory($sessionId, $limit = 50)
    {
        return self::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }
}
