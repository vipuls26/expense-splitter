<?php

namespace App\Events\group;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $groupId;
    public array $memberIds;

    public function __construct(int $groupId, array $memberIds = [])
    {
        $this->groupId = $groupId;
        $this->memberIds = $memberIds;
    }

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('group.' . $this->groupId)];
        
        foreach ($this->memberIds as $memberId) {
            $channels[] = new PrivateChannel('user.' . $memberId);
        }
        
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'GroupDeleted';
    }
}
