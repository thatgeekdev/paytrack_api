<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\WithdrawRequest;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $transactions = $this->transactionService->getTransactions(
            $request->only('type')
        );

        return response()->json($transactions);
    }
    
    public function deposit(DepositRequest $request)
    {
        $wallet = $this->transactionService->deposit($request->amount);

        return response()->json([
            'message' => 'Deposit successful',
            'balance' => $wallet->balance
        ]);
    }

    public function withdraw(WithdrawRequest $request)
    {
        $wallet = $this->transactionService->withdraw($request->amount);

        return response()->json([
            'message' => 'Withdraw successful',
            'balance' => $wallet->balance
        ]);
    }

    public function transfer(TransferRequest $request)
    {
        $result = $this->transactionService->transfer(
            $request->receiver_id,
            $request->amount
        );

        return response()->json([
            'message' => 'Transfer successful',
            'balance' => $result['sender_balance']
        ]);
    }
    
}
