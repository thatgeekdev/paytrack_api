<?php

namespace App\Repositories;

use App\Models\Wallet;

class WalletRepository
{
    public function getByUserId(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->first();
    }
    
    public function increment($wallet, $amount)
    {
        $wallet->increment('balance', $amount);
    }

    public function decrement($wallet, $amount)
    {
        $wallet->decrement('balance', $amount);
    }
}
