<?php

namespace App\Services;

namespace App\Services;

use App\Repositories\WalletRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionService
{
    protected $walletRepo;
    protected $transactionRepo;

    public function __construct(
        WalletRepository $walletRepo,
        TransactionRepository $transactionRepo
    ) {
        $this->walletRepo = $walletRepo;
        $this->transactionRepo = $transactionRepo;
    }

    public function deposit(float $amount)
    {
        $userId = Auth::id();
        $wallet = $this->walletRepo->getByUserId($userId);

        DB::beginTransaction();

        try {
            $this->walletRepo->increment($wallet, $amount);

            $this->transactionRepo->create([
                'type' => 'deposit',
                'amount' => $amount,
                'user_id' => $userId
            ]);

            DB::commit();

            return $wallet->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function withdraw(float $amount)
    {
        $userId = Auth::id();
        $wallet = $this->walletRepo->getByUserId($userId);

        if ($wallet->balance < $amount) {
            throw new Exception("Insufficient balance");
        }

        DB::beginTransaction();

        try {
            $this->walletRepo->decrement($wallet, $amount);

            $this->transactionRepo->create([
                'type' => 'withdraw',
                'amount' => $amount,
                'user_id' => $userId
            ]);

            DB::commit();

            return $wallet->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}