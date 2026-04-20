<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransferToSelfException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'error' => 'TRANSFER_TO_SELF',
            'message' => 'Transfer to self is not allowed'
        ], 400);
    }
}
