<?php

namespace App\Events\group;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $group;
    public int $groupId;

    public function __construct(int $groupId, array $group)
    {
        $this->groupId = $groupId;
        $this->group = $group;
    }

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('group.' . $this->groupId)];
        
        if (isset($this->group['members'])) {
            foreach ($this->group['members'] as $member) {
                $channels[] = new PrivateChannel('user.' . $member['id']);
            }
        }
        
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'GroupUpdated';
    }
}
