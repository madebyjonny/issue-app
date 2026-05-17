<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
        $message->load(['user', 'thread']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel.' . $this->message->channel_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->message->id,
            'parent_id'  => $this->message->parent_id,
            'body'       => $this->message->body,
            'channel_id' => $this->message->channel_id,
            'user'       => [
                'id'   => $this->message->user->id,
                'name' => $this->message->user->name,
            ],
            'mentioned_tickets' => $this->message->mentioned_tickets,
            'created_at' => $this->message->created_at->toISOString(),
        ];
    }
}
