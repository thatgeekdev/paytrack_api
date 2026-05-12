<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idempotency_key' => 'required|numericic|min:1|unique:transactions,idempotency_key',
            'receiver_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1'
        ];
    }
}
