<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Notifications\UserNotification;
use App\Services\DepositApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DepositController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $deposits = Transaction::with(['user', 'paymentMethod', 'paymentAddress'])
            ->when($request->boolean('trashed'), fn ($q) => $q->withTrashed())
            ->where('type', 'deposit')
            ->latest()
            ->paginate(20);

        $deposits->getCollection()->transform(function ($d) {
            $d->proof_url = $d->proof ? Storage::url($d->proof) : null;
            return $d;
        });

        return response()->json($deposits);
    }

    public function approve(Transaction $transaction, DepositApprovalService $service): JsonResponse
    {
        $this->authorize('approve', $transaction);
        $service->approve($transaction);

        $transaction->user->notify(new UserNotification(
            title: 'تم قبول الإيداع',
            body: "تم إضافة \${$transaction->amount} إلى محفظتك بنجاح.",
            url: '/wallet',
        ));

        return response()->json(['message' => 'Deposit approved']);
    }

    public function reject(Transaction $transaction): JsonResponse
    {
        $this->authorize('approve', $transaction);

        if ($transaction->status !== 'pending') {
            return response()->json(['error' => 'Transaction already processed.'], 422);
        }

        $transaction->update(['status' => 'rejected']);

        $transaction->user->notify(new UserNotification(
            title: 'تم رفض الإيداع',
            body: "إيداع \${$transaction->amount} لم يتم قبوله. تواصل مع الدعم للمزيد.",
            url: '/deposit',
        ));

        return response()->json(['message' => 'Deposit rejected']);
    }
}