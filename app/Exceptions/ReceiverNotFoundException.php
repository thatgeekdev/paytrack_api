<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class ReceiverNotFoundException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'error' => 'RECEIVER_NOT_FOUND',
            'message' => 'Receiver not found'
        ], 404);
    }

}
