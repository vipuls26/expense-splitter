<?php

namespace App\Events\member;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberLeftGroup implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $groupId;
    public int $userId;
    public string $userName;
    public array $remainingMemberIds;

    public function __construct(int $groupId, int $userId, string $userName, array $remainingMemberIds = [])
    {
        $this->groupId = $groupId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->remainingMemberIds = $remainingMemberIds;
    }

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('group.' . $this->groupId)];
        
        foreach ($this->remainingMemberIds as $memberId) {
            $channels[] = new PrivateChannel('user.' . $memberId);
        }
        
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'MemberLeftGroup';
    }
}
