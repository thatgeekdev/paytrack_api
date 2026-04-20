<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\TransactionResource;
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
        $filters = $request->validate([
            'type' => 'nullable|in:deposit,withdraw,transfer',
            'min_amount' => 'nullable|numeric',
            'max_amount' => 'nullable|numeric',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);
        $transactions = $this->transactionService->getTransactions($filters);

        // return response()->json($transactions);
        return TransactionResource::collection($transactions);
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
