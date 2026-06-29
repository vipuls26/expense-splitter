<?php

namespace App\Events\expense;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $groupId;
    public array $expense;

    public function __construct(int $groupId, array $expense)
    {
        $this->groupId = $groupId;
        $this->expense = $expense;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('group.' . $this->groupId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ExpenseUpdated';
    }
}
