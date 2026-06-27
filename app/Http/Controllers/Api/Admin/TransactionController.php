<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\PaymentApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with(['user', 'order' => fn ($q) => $q->withTrashed()])
            ->when($request->boolean('trashed'), fn ($q) => $q->withTrashed())
            ->where('type', '!=', 'deposit')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->paginate(20));
    }

    public function approve(Transaction $transaction, PaymentApprovalService $approvalService): JsonResponse
    {
        $this->authorize('approve', $transaction);

        if ($transaction->status !== 'pending') {
            return response()->json(['error' => 'Transaction already processed.'], 422);
        }

        $approvalService->approve($transaction);

        return response()->json(['message' => 'Payment approved and order fulfilled.']);
    }
}