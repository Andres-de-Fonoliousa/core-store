<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PaymentApprovalService
{
    public function __construct(
        protected FulfillmentService $fulfillmentService
    ) {}

    public function approve(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $user = $transaction->user;
            $order = $transaction->order;

            // 1. Mark transaction completed
            $before = $user->balance;
            $user->decrement('balance', abs($transaction->amount));
            $after = $user->fresh()->balance;

            $transaction->update([
                'status'         => 'completed',
                'balance_before' => $before,
                'balance_after'  => $after,
            ]);

            // 2. Mark order as paid
            $order->update(['status' => 'paid']);

            // 3. If auto-fulfill, get code
            if ($order->product->is_auto) {
                $serial = $this->fulfillmentService->dispatch($order->product, $order);
                $order->update([
                    'serial_code'        => $serial,  // automatically encrypted via cast
                    'fulfillment_status' => 'fulfilled',
                ]);
            } else {
                // manual fulfillment (maybe admin inputs code later)
                $order->update(['fulfillment_status' => 'pending_manual']);
            }
        });
    }
}