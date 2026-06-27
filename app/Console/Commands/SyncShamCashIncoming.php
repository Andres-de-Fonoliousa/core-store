<?php

namespace App\Console\Commands;

use App\Models\ShamCashPayment;
use App\Models\Transaction;
use App\Services\DepositService;
use App\Services\ShamCashBridgeService;
use Illuminate\Console\Command;

class SyncShamCashIncoming extends Command
{
    protected $signature = 'sham-cash:sync-incoming';

    protected $description = 'Fetch incoming Sham Cash transactions from bridge and match pending deposits';

    public function handle(ShamCashBridgeService $bridge, DepositService $depositService): int
    {
        $lastSync = ShamCashPayment::max('created_at');
        $since = $lastSync ? $lastSync->subHour() : now()->subDay();

        $transactions = $bridge->fetchIncoming($since);

        if (empty($transactions)) {
            $this->info('No incoming transactions from bridge.');
            return Command::SUCCESS;
        }

        $new = 0;
        $matched = 0;

        foreach ($transactions as $tx) {
            $tranId = $tx['tranId'] ?? ($tx['tran_id'] ?? null);
            if (!$tranId) continue;

            $existing = ShamCashPayment::where('tran_id', $tranId)->exists();
            if ($existing) continue;

            ShamCashPayment::create([
                'tran_id'         => $tranId,
                'amount'          => $tx['amount'] ?? 0,
                'currency_id'     => $tx['currencyId'] ?? 1,
                'currency_name'   => $tx['currencyName'] ?? 'USD',
                'sender_name'     => $tx['peerUserName'] ?? null,
                'sender_account'  => $tx['peerAccountNumber'] ?? null,
                'sender_address'  => $tx['peerAccountAddress'] ?? null,
                'note'            => $tx['note'] ?? null,
                'tran_date'       => $tx['tranDate'] ?? now()->toDateString(),
                'tran_time'       => $tx['tranTime'] ?? now()->toTimeString(),
                'raw'             => $tx,
            ]);

            $new++;

            $pending = Transaction::where('payment_id', (string) $tranId)
                ->where('type', 'deposit')
                ->where('status', 'pending')
                ->first();

            if ($pending) {
                $depositService->verifyShamCashTransaction(
                    (string) $tranId,
                    $tx['amount'] ?? 0,
                    $pending,
                );
                $matched++;
            }
        }

        $this->info("Synced {$new} new transactions, matched {$matched} deposits.");

        return Command::SUCCESS;
    }
}
