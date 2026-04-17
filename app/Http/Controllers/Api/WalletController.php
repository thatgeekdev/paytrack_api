<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WalletService;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function show()
    {
        $wallet = $this->walletService->getUserWallet();

        return response()->json([
            'balance' => $wallet->balance
        ]);
    }
}
