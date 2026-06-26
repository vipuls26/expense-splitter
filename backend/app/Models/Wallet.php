<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'balance'])]
class Wallet extends Model
{
    protected $casts = [
        'balance' => 'decimal:2',
    ];

    // wallet owner
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // wallet transactions
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
