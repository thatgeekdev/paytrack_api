<?php

namespace App\Services;

use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\Auth;

class WalletService
{
    protected $walletRepository;

    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function getUserWallet()
    {
        $userId = Auth::id();

        return $this->walletRepository->getByUserId($userId);
    }
}