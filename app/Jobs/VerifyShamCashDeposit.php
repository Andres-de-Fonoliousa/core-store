<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\DepositService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class VerifyShamCashDeposit implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public int $transactionId,
    ) {}

    public function handle(DepositService $service): void
    {
        $transaction = Transaction::find($this->transactionId);

        if (!$transaction || $transaction->status !== 'pending') {
            return;
        }

        $verified = $service->retryVerification($transaction);

        if (!$verified && $transaction->status === 'pending') {
            VerifyShamCashDeposit::dispatch($this->transactionId)
                ->delay(now()->addMinute());
        }
    }
}
