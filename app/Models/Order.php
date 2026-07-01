<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Concerns\HasTenantScope;
use App\Traits\LogsActivity;

#[Fillable(['user_id', 'product_id', 'price_at_time_of_order', 'quantity', 'status', 'details', 'transaction_id', 'serial_code', 'fulfillment_status', 'fail_reason', 'payment_method_id', 'payment_type'])]
class Order extends Model
{
    use SoftDeletes, LogsActivity, HasTenantScope;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    protected function casts(): array
    {
        return [
            'price_at_time_of_order' => 'decimal:2',
            'details' => 'array',
            'serial_code' => 'encrypted',
        ];
    }
}
