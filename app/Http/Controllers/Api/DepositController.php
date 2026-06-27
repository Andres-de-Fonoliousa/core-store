<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\DepositService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DepositController extends Controller
{
    public function __construct(
        protected DepositService $depositService,
    ) {}

    public function show(Transaction $transaction): JsonResponse
    {
        if ($transaction->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $transaction->load(['paymentMethod', 'paymentAddress']);
        $transaction->proof_url = $transaction->proof ? Storage::url($transaction->proof) : null;

        return response()->json($transaction);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount'              => 'required|numeric|min:1',
            'payment_method_id'   => 'required|exists:payment_methods,id',
            'payment_address_id'  => 'nullable|exists:payment_addresses,id',
            'proof'               => 'nullable|image|max:2048',
            'tran_id'             => 'nullable|string|max:50',
            'note'                => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $method = \App\Models\PaymentMethod::findOrFail($validated['payment_method_id']);

        if ($method->code === 'manual_screenshot' && !$request->hasFile('proof')) {
            return response()->json(['error' => 'Proof image is required for manual deposits'], 422);
        }

        if ($method->code === 'sham_cash' && empty($validated['tran_id'])) {
            return response()->json(['error' => 'Transaction ID is required for Sham Cash deposits'], 422);
        }

        $transaction = $this->depositService->createDeposit($validated, $user);
        $transaction->load(['paymentMethod', 'paymentAddress']);
        $transaction->proof_url = $transaction->proof ? Storage::url($transaction->proof) : null;

        return response()->json([
            'message' => 'Deposit submitted successfully',
            'transaction' => $transaction,
        ], 201);
    }
}
