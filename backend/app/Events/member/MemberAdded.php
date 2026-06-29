<?php

namespace App\Events\member;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberAdded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $groupId;
    public array $member;

    public function __construct(int $groupId, array $member)
    {
        $this->groupId = $groupId;
        $this->member = $member;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('group.' . $this->groupId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MemberAdded';
    }
}
