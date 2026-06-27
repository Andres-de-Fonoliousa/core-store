<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShamCashPayment;
use App\Services\DepositService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShamCashWebhookController extends Controller
{
    public function __construct(
        protected DepositService $depositService,
    ) {}

    public function incoming(Request $request): JsonResponse
    {
        $secret = config('services.sham_cash.webhook_secret');
        if ($secret && $request->header('X-Sham-Secret') !== $secret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'transactions' => 'required|array',
            'transactions.*.tranId' => 'required|integer',
            'transactions.*.amount' => 'required|numeric',
            'transactions.*.currencyId' => 'required|integer',
            'transactions.*.currencyName' => 'nullable|string',
            'transactions.*.peerUserName' => 'nullable|string',
            'transactions.*.peerAccountNumber' => 'nullable|string',
            'transactions.*.peerAccountAddress' => 'nullable|string',
            'transactions.*.note' => 'nullable|string',
            'transactions.*.tranDate' => 'nullable|string',
            'transactions.*.tranTime' => 'nullable|string',
        ]);

        $matched = 0;

        foreach ($validated['transactions'] as $tx) {
            $tranId = $tx['tranId'];

            ShamCashPayment::updateOrCreate(
                ['tran_id' => $tranId],
                [
                    'amount'         => $tx['amount'],
                    'currency_id'    => $tx['currencyId'],
                    'currency_name'  => $tx['currencyName'] ?? 'USD',
                    'sender_name'    => $tx['peerUserName'] ?? null,
                    'sender_account' => $tx['peerAccountNumber'] ?? null,
                    'sender_address' => $tx['peerAccountAddress'] ?? null,
                    'note'           => $tx['note'] ?? null,
                    'tran_date'      => $tx['tranDate'] ?? now()->toDateString(),
                    'tran_time'      => $tx['tranTime'] ?? now()->toTimeString(),
                    'raw'            => $tx,
                ],
            );

            $transaction = \App\Models\Transaction::where('payment_id', (string) $tranId)
                ->where('type', 'deposit')
                ->where('status', 'pending')
                ->first();

            if ($transaction) {
                $this->depositService->verifyShamCashTransaction(
                    (string) $tranId,
                    $tx['amount'],
                    $transaction,
                );
                $matched++;
            }
        }

        return response()->json([
            'received' => count($validated['transactions']),
            'matched' => $matched,
        ]);
    }
}
