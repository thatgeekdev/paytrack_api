<?php

namespace App\Support;

use Illuminate\Support\Str;

class TransactionReference
{
    public static function generate(): string
    {
        return 'TXN-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
    }

    // Prefixo (TXN) - identifica tipo | Timestamp - ordenável e rastreável | Random - evita colisão
}