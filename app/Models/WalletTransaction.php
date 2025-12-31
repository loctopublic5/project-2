<?php

namespace App\Models;

use App\Models\UserWallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'reference_id',
        'description',
        'status'
    ];

    public function wallet(): BelongsTo{
        return $this->belongsTo(UserWallet::class);
    }
}
