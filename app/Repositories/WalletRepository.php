<?php

namespace App\Repositories;

use App\Models\Wallet;

class WalletRepository
{
    public function getByUserId(int $userId): ?Wallet
    {
        return Wallet::where('user_id', $userId)->first();
    }
}
