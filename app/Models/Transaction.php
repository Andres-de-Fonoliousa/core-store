<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

#[Fillable(['user_id', 'order_id', 'amount', 'type', 'status', 'balance_before', 'balance_after', 'payment_id', 'proof', 'note', 'date', 'payment_method_id', 'payment_address_id', 'verified_at'])]
class Transaction extends Model
{
    use SoftDeletes, LogsActivity;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function paymentAddress(): BelongsTo
    {
        return $this->belongsTo(PaymentAddress::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'date' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }
}
