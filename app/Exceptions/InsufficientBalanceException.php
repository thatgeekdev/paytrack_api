<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class InsufficientBalanceException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'error' => 'INSUFFICIENT_BALANCE',
            'message' => 'Insufficient balance'
        ], 400);
    }
}
