<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $boardId,
        public readonly array $activity,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("boards.{$this->boardId}")];
    }

    public function broadcastWith(): array
    {
        return [
            'activity' => $this->activity,
        ];
    }

    public function broadcastAs(): string
    {
        return 'activity.created';
    }
}
