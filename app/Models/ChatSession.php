<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** ERD entity: ChatSessions */
class ChatSession extends Model
{
    protected $table = 'chat_sessions';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'title', 'started_at', 'ended_at', 'status', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at'   => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'session_id')->orderBy('created_at');
    }

    /** Conversation formatted for the Groq chat completions endpoint. */
    public function toGroqMessages(): array
    {
        return $this->messages
            ->map(fn (ChatMessage $m) => [
                'role'    => $m->sender_type === 'user' ? 'user' : 'assistant',
                'content' => $m->message,
            ])
            ->all();
    }

    public function end(): void
    {
        $this->update(['status' => 'Ended', 'ended_at' => now()]);
    }
}
