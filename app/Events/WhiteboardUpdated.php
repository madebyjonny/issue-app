<?php

namespace App\Events;

use App\Models\Doc;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WhiteboardUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Doc   $doc,
        public array $elements,
        public int   $senderId,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("whiteboard.{$this->doc->id}")];
    }

    public function broadcastWith(): array
    {
        return [
            'elements'  => $this->elements,
            'sender_id' => $this->senderId,
        ];
    }
}
