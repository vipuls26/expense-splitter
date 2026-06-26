<?php

namespace App\Models;

use App\Enum\WalletTransactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'wallet_id',
    'type',
    'amount',
    'balance_before',
    'balance_after',
    'reference_type',
    'reference_id',
    'description',
])]

class WalletTransaction extends Model
{
    //

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'type' => WalletTransactionType::class,
    ];

    // wallet owner transction
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
