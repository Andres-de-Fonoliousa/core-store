<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShamCashPayment extends Model
{
    protected $fillable = [
        'tran_id', 'amount', 'currency_id', 'currency_name',
        'sender_name', 'sender_account', 'sender_address',
        'note', 'tran_date', 'tran_time', 'raw', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'raw' => 'array',
            'tran_date' => 'date',
            'processed_at' => 'datetime',
        ];
    }
}
