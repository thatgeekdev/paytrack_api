<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function deposit(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $wallet = $this->transactionService->deposit($data['amount']);

        return response()->json([
            'message' => 'Deposit successful',
            'balance' => $wallet->balance
        ]);
    }

    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $wallet = $this->transactionService->withdraw($data['amount']);

        return response()->json([
            'message' => 'Withdraw successful',
            'balance' => $wallet->balance
        ]);
    }
}
