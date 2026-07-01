<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['wallet_id', 'type', 'amount', 'balance_before', 'balance_after', 'description'])]
class WalletTransaction extends Model
{
    //
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
