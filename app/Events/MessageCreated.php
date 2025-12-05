<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('chat.'.$this->message->chat_id);
    }

    public function broadcastAs(): string
    {
        return 'message.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'sender' => $this->message->sender,
            'body' => $this->message->body,
            'is_ai' => $this->message->is_ai,
            'chat_id' => $this->message->chat_id,
            'sender_id' => $this->message->sender_id,
            'created_at' => $this->message->created_at?->toISOString(),
        ];
    }
}
