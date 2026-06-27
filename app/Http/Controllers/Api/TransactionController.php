<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $transactions = Transaction::with('order')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->integer('per_page', 50));

        return response()->json($transactions);
    }
}
