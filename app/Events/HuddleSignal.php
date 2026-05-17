<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** WebRTC signaling for huddle (offer / answer / ice-candidate / hangup) */
class HuddleSignal implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomId,
        public int $fromUserId,
        public int $toUserId,
        public string $type,   // offer | answer | ice-candidate | hangup
        public mixed $payload
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('huddle.user.' . $this->toUserId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'room_id'      => $this->roomId,
            'from_user_id' => $this->fromUserId,
            'type'         => $this->type,
            'payload'      => $this->payload,
        ];
    }
}
