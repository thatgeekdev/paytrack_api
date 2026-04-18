<?php

namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepository
{
    public function create(array $data)
    {
        return Transaction::create($data);
    }
    
    public function createTransfer(array $data)
    {
        return Transaction::create($data);
    }
        public function getUserTransactions($userId, $filters = [])
    {
        $query = Transaction::where('user_id', $userId);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->latest()->paginate(10);
    }
}

