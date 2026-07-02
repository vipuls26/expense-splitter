<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {

        Wallet::create([
            'user_id' => $user->id,
            'balance' => 0
        ]);

        Log::info('wallet created for: ', [
            'user_id' => $user->id,
            'user_name' => $user->name,
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
