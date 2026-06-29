<?php

namespace App\Events\budget;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BudgetUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $groupId;
    public array $budget;

    public function __construct(int $groupId, array $budget)
    {
        $this->groupId = $groupId;
        $this->budget = $budget;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('group.' . $this->groupId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'BudgetUpdated';
    }
}
