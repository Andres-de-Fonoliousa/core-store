<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\DepositService;
use Illuminate\Console\Command;

class RetryShamCashDeposits extends Command
{
    protected $signature = 'sham-cash:retry-pending';

    protected $description = 'Retry verification of pending Sham Cash deposits';

    public function handle(DepositService $service): int
    {
        $transactions = Transaction::where('type', 'deposit')
            ->where('status', 'pending')
            ->whereHas('paymentMethod', fn ($q) => $q->where('code', 'sham_cash'))
            ->where('created_at', '<=', now()->subMinute())
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('No pending Sham Cash deposits to retry.');
            return Command::SUCCESS;
        }

        $verified = 0;
        $failed = 0;
        $stillPending = 0;

        foreach ($transactions as $transaction) {
            $result = $service->retryVerification($transaction);

            if ($result) {
                $verified++;
                $this->info("Transaction #{$transaction->id} verified.");
            } elseif ($transaction->status === 'failed') {
                $failed++;
                $this->warn("Transaction #{$transaction->id} expired and failed.");
            } else {
                $stillPending++;
            }
        }

        $this->info("Done: {$verified} verified, {$failed} failed, {$stillPending} still pending.");

        return Command::SUCCESS;
    }
}
