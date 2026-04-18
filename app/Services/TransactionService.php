<?php

namespace App\Services;

namespace App\Services;

use App\Repositories\WalletRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Wallet;
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

    public function transfer(int $receiverId, float $amount)
    {
        $senderId = Auth::id();

        if ($senderId == $receiverId) {
            throw new Exception("Cannot transfer to yourself");
        }

        DB::beginTransaction();

        try {
            //faco um lock da linha do remetente e do destinatário na BD para evitar condições de corrida
            $senderWallet = Wallet::where('user_id', $senderId)
                ->lockForUpdate()
                ->first();

            $receiverWallet = Wallet::where('user_id', $receiverId)
                ->lockForUpdate()
                ->first();

            if (!$receiverWallet) {
                throw new Exception("Receiver not found");
            }

            if ($senderWallet->balance < $amount) {
                throw new Exception("Insufficient balance");
            }

            // debit sender
            $this->walletRepo->decrement($senderWallet, $amount);

            // credit receiver
            $this->walletRepo->increment($receiverWallet, $amount);

            // record transaction
            $this->transactionRepo->createTransfer([
                'type' => 'transfer',
                'amount' => $amount,
                'user_id' => $senderId,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);

            DB::commit();

            return [
                'sender_balance' => $senderWallet->fresh()->balance
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}