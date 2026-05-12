<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'uuid',
        'reference',
        'idempotency_key',
        'type',
        'amount',
        'user_id',
        'sender_id',
        'receiver_id',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}