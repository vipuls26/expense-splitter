<?php

namespace App\Events\wallet;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletDeposited implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public float $walletBalance;
    public array $transaction;

    public function __construct(int $userId, float $walletBalance, array $transaction)
    {
        $this->userId = $userId;
        $this->walletBalance = $walletBalance;
        $this->transaction = $transaction;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'WalletDeposited';
    }
}
