<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ColumnReordered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $boardId,
        public readonly array $columns,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("boards.{$this->boardId}")];
    }

    public function broadcastWith(): array
    {
        return [
            'columns' => $this->columns,
        ];
    }

    public function broadcastAs(): string
    {
        return 'column.reordered';
    }
}
