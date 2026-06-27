<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    public function index(): JsonResponse
    {
        $methods = PaymentMethod::with(['addresses' => fn ($q) => $q->active()->orderBy('sort')])
            ->active()
            ->orderBy('sort')
            ->get();

        return response()->json($methods);
    }

    public function addresses(PaymentMethod $paymentMethod): JsonResponse
    {
        $addresses = $paymentMethod->addresses()
            ->active()
            ->orderBy('sort')
            ->get();

        return response()->json($addresses);
    }
}
