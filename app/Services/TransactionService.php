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

            return $wallet->fresh();
            
            Log::info('Deposit successful', [
                'user_id' => $userId,
                'amount' => $amount,
                'new_balance' => $wallet->balance
            ]);
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

            return $wallet->fresh();
            Log::info('Withdraw successful', [
                'user_id' => $userId,
                'amount' => $amount,
                'new_balance' => $wallet->balance
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
            
            Log::error('Withdraw failed', [
                'user_id' => $userId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function transfer(int $receiverId, float $amount)
    {
        $senderId = Auth::id();

        if ($senderId == $receiverId) {
            throw new TransferToSelfException();
        }

        $receiver = User::with('wallet')->find($receiverId);
        if (!$receiver || !$receiver->wallet) {
            throw new ReceiverNotFoundException();
        }
        // if ($receiver->status !== 'active') {
        //     throw new ReceiverNotFoundException();
        // }

        DB::beginTransaction();

        try {
            Log::info('Transfer started', [
                'from' => $senderId,
                'to' => $receiverId,
                'amount' => $amount
            ]);

            //faco um lock da linha do remetente e do destinatário na BD para evitar condições de corrida
            $senderWallet = $this->walletRepo->getForUpdate($senderId);
            $receiverWallet = $this->walletRepo->getForUpdate($receiverId);

            if (!$receiverWallet) {
                throw new TransferToSelfException();
            }

            if ($senderWallet->balance < $amount) {
                throw new InsufficientBalanceException();
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

            Log::info('Transfer successful', [
                'from' => $senderId,
                'to' => $receiverId,
                'amount' => $amount,
                'sender_new_balance' => $senderWallet->balance,
                'receiver_new_balance' => $receiverWallet->balance
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;

            Log::error('Transfer failed', [
                'from' => $senderId,
                'to' => $receiverId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getTransactions($filters)
    {
        return $this->transactionRepo->getUserTransactions(Auth::id(), $filters);
    }
}