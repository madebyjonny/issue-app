<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HuddleUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int   $projectId,
        public ?int  $huddleId,
        public bool  $isActive,
        public array $participants // [['id' => int, 'name' => string], ...]
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('project.' . $this->projectId)];
    }

    public function broadcastWith(): array
    {
        return [
            'huddle_id'    => $this->huddleId,
            'is_active'    => $this->isActive,
            'participants' => $this->participants,
        ];
    }
}
