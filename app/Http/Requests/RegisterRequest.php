<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
// use Illuminate\Support\Facades\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            // 'password' => ['required', Password::min(6)->mixedCase()->numbers()->symbols()] // solucao nativa do laravel, mas com algum erro na validacao de simbolos
            'password' => ['required','min:8','regex:/[A-Z]/','regex:/[0-9]/','regex:/[@$!%*#?&]/']
        ];
    }
}
