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

    public function getUserTransactions(int $userId, array $filters = [])
    {
        $query = Transaction::query()
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('receiver_id', $userId);
            });

        // filtro por tipo
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // valor mínimo
        if (!empty($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }

        // valor máximo
        if (!empty($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        // data início
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        // data fim
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate(10);
    }
}

