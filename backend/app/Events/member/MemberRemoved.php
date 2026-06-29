<?php

namespace App\Events\member;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberRemoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $groupId;
    public int $userId;
    public string $userName;
    public array $allMemberIds;

    public function __construct(int $groupId, int $userId, string $userName, array $allMemberIds = [])
    {
        $this->groupId = $groupId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->allMemberIds = $allMemberIds;
    }

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('group.' . $this->groupId)];
        
        foreach ($this->allMemberIds as $memberId) {
            $channels[] = new PrivateChannel('user.' . $memberId);
        }
        
        // Ensure the removed user also gets it if they were just removed from the list
        if (!in_array($this->userId, $this->allMemberIds)) {
            $channels[] = new PrivateChannel('user.' . $this->userId);
        }
        
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'MemberRemoved';
    }
}
