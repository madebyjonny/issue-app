<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Broadcast cursor position updates on the board */
class CursorMoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $projectId,
        public int $userId,
        public string $userName,
        public ?int $ticketId,
        public ?string $columnId,
        public ?array $position   // ['x' => float, 'y' => float]
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('board.' . $this->projectId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'user_id'   => $this->userId,
            'user_name' => $this->userName,
            'ticket_id' => $this->ticketId,
            'column_id' => $this->columnId,
            'position'  => $this->position,
        ];
    }
}
