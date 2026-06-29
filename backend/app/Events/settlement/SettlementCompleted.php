<?php

namespace App\Events\settlement;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettlementCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $groupId;
    public array $settlement;

    public function __construct(int $groupId, array $settlement)
    {
        $this->groupId = $groupId;
        $this->settlement = $settlement;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('group.' . $this->groupId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'SettlementCompleted';
    }
}
