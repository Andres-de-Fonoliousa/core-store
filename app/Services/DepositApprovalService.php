<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DepositApprovalService
{
    public function approve(Transaction $transaction): void
    {
        if ($transaction->type !== 'deposit' || $transaction->status !== 'pending') {
            throw new \Exception('Invalid transaction');
        }

        DB::transaction(function () use ($transaction) {
            $user = $transaction->user;
            $before = $user->balance;
            $user->increment('balance', $transaction->amount);
            $after = $user->fresh()->balance;

            $transaction->update([
                'status'         => 'completed',
                'balance_before' => $before,
                'balance_after'  => $after,
                'verified_at'    => now(),
            ]);
        });
    }
}