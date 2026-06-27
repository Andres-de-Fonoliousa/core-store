<?php

namespace App\Services;

use App\Models\PaymentAddress;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DepositService
{
    public function __construct(
        protected ShamCashBridgeService $shamCash,
        protected DepositApprovalService $approval,
    ) {}

    public function createDeposit(array $data, User $user): Transaction
    {
        $method = PaymentMethod::findOrFail($data['payment_method_id']);

        if ($method->code === 'sham_cash') {
            return $this->createShamCashDeposit($data, $user, $method);
        }

        return $this->createManualDeposit($data, $user, $method);
    }

    protected function createManualDeposit(array $data, User $user, PaymentMethod $method): Transaction
    {
        $proof = $data['proof'];
        $path = $proof->store('deposits', 'public');

        $transaction = Transaction::create([
            'user_id'           => $user->id,
            'amount'            => $data['amount'],
            'type'              => 'deposit',
            'status'            => 'pending',
            'proof'             => $path,
            'note'              => $data['note'] ?? null,
            'date'              => now(),
            'payment_method_id' => $method->id,
            'payment_address_id' => $data['payment_address_id'] ?? null,
        ]);

        return $transaction;
    }

    protected function createShamCashDeposit(array $data, User $user, PaymentMethod $method): Transaction
    {
        $transaction = Transaction::create([
            'user_id'           => $user->id,
            'amount'            => $data['amount'],
            'type'              => 'deposit',
            'status'            => 'pending',
            'payment_id'        => $data['tran_id'],
            'date'              => now(),
            'payment_method_id' => $method->id,
            'payment_address_id' => $data['payment_address_id'] ?? null,
        ]);

        $verified = $this->verifyShamCashTransaction(
            $data['tran_id'],
            $data['amount'],
            $transaction,
        );

        if (!$verified) {
            $this->queueRetryVerification($transaction);
        }

        return $transaction;
    }

    public function verifyShamCashTransaction(string $tranId, float $amount, Transaction $transaction): bool
    {
        $result = $this->shamCash->checkTransaction($tranId, $amount);

        if (($result['found'] ?? false)) {
            DB::transaction(function () use ($transaction, $result) {
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

                $this->recordShamCashPayment($result['transaction'] ?? []);
            });

            $transaction->user->notify(new UserNotification(
                title: 'تم تأكيد الإيداع عبر Sham Cash',
                body: "تم إضافة \${$transaction->amount} إلى محفظتك بنجاح.",
                url: '/wallet',
            ));

            return true;
        }

        return false;
    }

    protected function recordShamCashPayment(array $txData): void
    {
        if (empty($txData)) return;

        try {
            \App\Models\ShamCashPayment::updateOrCreate(
                ['tran_id' => $txData['tranId'] ?? $txData['tran_id'] ?? 0],
                [
                    'amount'         => $txData['amount'] ?? 0,
                    'currency_id'    => $txData['currencyId'] ?? 1,
                    'currency_name'  => $txData['currencyName'] ?? 'USD',
                    'sender_name'    => $txData['peerUserName'] ?? null,
                    'sender_account' => $txData['peerAccountNumber'] ?? null,
                    'sender_address' => $txData['peerAccountAddress'] ?? null,
                    'note'           => $txData['note'] ?? null,
                    'tran_date'      => $txData['tranDate'] ?? now()->toDateString(),
                    'tran_time'      => $txData['tranTime'] ?? now()->toTimeString(),
                    'raw'            => $txData,
                    'processed_at'   => now(),
                ],
            );
        } catch (\Exception $e) {
            Log::warning('Failed to record ShamCash payment', [
                'error' => $e->getMessage(),
                'data' => $txData,
            ]);
        }
    }

    protected function queueRetryVerification(Transaction $transaction): void
    {
        \App\Jobs\VerifyShamCashDeposit::dispatch($transaction->id)
            ->delay(now()->addMinute());
    }

    public function retryVerification(Transaction $transaction): bool
    {
        if ($transaction->status !== 'pending') {
            return false;
        }

        $tranId = $transaction->payment_id;
        if (!$tranId) {
            return false;
        }

        $maxAge = now()->subMinutes(10);

        if ($transaction->created_at->lt($maxAge)) {
            $transaction->update(['status' => 'failed']);
            $transaction->user->notify(new UserNotification(
                title: 'فشل التحقق من الإيداع',
                body: "لم نتمكن من تأكيد إيداع \${$transaction->amount} عبر Sham Cash. تواصل مع الدعم الفني.",
                url: '/deposit',
            ));
            return false;
        }

        return $this->verifyShamCashTransaction($tranId, $transaction->amount, $transaction);
    }
}
