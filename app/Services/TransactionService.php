<?php

namespace App\Services;

namespace App\Services;

use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\ReceiverNotFoundException;
use App\Exceptions\TransferToSelfException;
use App\Models\User;
use App\Repositories\WalletRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Support\TransactionReference;
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

            Log::info('Deposit started', [
                'user_id' => $userId,
                'amount' => $amount
            ]);
            $this->walletRepo->increment($wallet, $amount);

            $this->transactionRepo->create([
                'type' => 'deposit',
                'amount' => $amount,
                'user_id' => $userId
            ]);

            DB::commit();

            Log::info('Deposit successful', [
                'user_id' => $userId,
                'amount' => $amount,
                'new_balance' => $wallet->balance
            ]);

            return $wallet->fresh();
            

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
            
            Log::error('Deposit failed', [
                'user_id' => $userId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function withdraw(float $amount)
    {
        $userId = Auth::id();
        $wallet = $this->walletRepo->getByUserId($userId);

        if ($wallet->balance < $amount) {
            throw new InsufficientBalanceException();
        }

        DB::beginTransaction();

        try {
            Log::info('Withdraw started', [
                'user_id' => $userId,
                'amount' => $amount
            ]);

            $this->walletRepo->decrement($wallet, $amount);

            $this->transactionRepo->create([
                'type' => 'withdraw',
                'amount' => $amount,
                'user_id' => $userId
            ]);

            DB::commit();

            Log::info('Withdraw successful', [
                'user_id' => $userId,
                'amount' => $amount,
                'new_balance' => $wallet->balance
            ]);
            
            return $wallet->fresh();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Withdraw failed', [
                'user_id' => $userId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]); 
                       
            throw $e;
            

        }
    }

    // public function transfer(int $receiverId, float $amount)
    // {
    //     $senderId = Auth::id();

    //     if ($senderId == $receiverId) {
    //         throw new TransferToSelfException();
    //     }

    //     $receiver = User::with('wallet')->find($receiverId);
    //     if (!$receiver || !$receiver->wallet) {
    //         throw new ReceiverNotFoundException();
    //     }
    //     // if ($receiver->status !== 'active') {
    //     //     throw new ReceiverNotFoundException();
    //     // }

    //     DB::beginTransaction();

    //     try {
    //         Log::info('Transfer started', [
    //             'from' => $senderId,
    //             'to' => $receiverId,
    //             'amount' => $amount
    //         ]);

    //         //faco um lock da linha do remetente e do destinatário na BD para evitar condições de corrida
    //         $senderWallet = $this->walletRepo->getForUpdate($senderId);
    //         $receiverWallet = $this->walletRepo->getForUpdate($receiverId);

    //         if (!$receiverWallet) {
    //             throw new TransferToSelfException();
    //         }

    //         if ($senderWallet->balance < $amount) {
    //             throw new InsufficientBalanceException();
    //         }

    //         // debit sender
    //         $this->walletRepo->decrement($senderWallet, $amount);

    //         // credit receiver
    //         $this->walletRepo->increment($receiverWallet, $amount);

    //         // record transaction
    //         $this->transactionRepo->createTransfer([
    //             'type' => 'transfer',
    //             'amount' => $amount,
    //             'user_id' => $senderId,
    //             'sender_id' => $senderId,
    //             'receiver_id' => $receiverId
    //         ]);

    //         DB::commit();

    //         return [
    //             'sender_balance' => $senderWallet->fresh()->balance
    //         ];

    //         Log::info('Transfer successful', [
    //             'from' => $senderId,
    //             'to' => $receiverId,
    //             'amount' => $amount,
    //             'sender_new_balance' => $senderWallet->balance,
    //             'receiver_new_balance' => $receiverWallet->balance
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw $e;

    //         Log::error('Transfer failed', [
    //             'from' => $senderId,
    //             'to' => $receiverId,
    //             'amount' => $amount,
    //             'error' => $e->getMessage()
    //         ]);
    //     }
    // }
    public function transfer(int $receiverId, float $amount, string $idempotencyKey)
    {
        $senderId = Auth::id();

        if ($senderId == $receiverId) {
            throw new TransferToSelfException();
        }

        // 🔁 IDEMPOTÊNCIA
        $existing = $this->transactionRepo->findByIdempotencyKey($idempotencyKey);
        if ($existing) {
            return $existing;
        }

        $receiver = User::with('wallet')->find($receiverId);
        if (!$receiver || !$receiver->wallet) {
            throw new ReceiverNotFoundException();
        }

        DB::beginTransaction();

        try {
            Log::info('Transfer started', [
                'from' => $senderId,
                'to' => $receiverId,
                'amount' => $amount
            ]);

            // 🔒 ORDEM PARA EVITAR DEADLOCK
            $ids = [$senderId, $receiverId];
            sort($ids);

            $wallet1 = $this->walletRepo->getForUpdate($ids[0]);
            $wallet2 = $this->walletRepo->getForUpdate($ids[1]);

            $senderWallet = $senderId === $ids[0] ? $wallet1 : $wallet2;
            $receiverWallet = $senderId === $ids[0] ? $wallet2 : $wallet1;

            if ($senderWallet->balance < $amount) {
                throw new InsufficientBalanceException();
            }

            // 💸 movimentação
            $this->walletRepo->decrement($senderWallet, $amount);
            $this->walletRepo->increment($receiverWallet, $amount);

            // 🧾 criar transação
            $transaction = $this->transactionRepo->createTransfer([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'reference' => TransactionReference::generate(),
                'idempotency_key' => $idempotencyKey,
                'type' => 'transfer',
                'amount' => $amount,
                'user_id' => $senderId,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'status' => 'success'
            ]);

            DB::commit();

            Log::info('Transfer successful', [
                'transaction_id' => $transaction->uuid
            ]);

            return $transaction;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Transfer failed', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function getTransactions($filters)
    {
        return $this->transactionRepo->getUserTransactions(Auth::id(), $filters);
    }
}