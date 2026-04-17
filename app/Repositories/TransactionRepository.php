<?php

namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepository
{
    /**
     * Create a new class instance.
     */
    public function create(array $data)
    {
        return Transaction::create($data);
    }
    
    public function createTransfer(array $data)
    {
        return Transaction::create($data);
    }
}

