<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentAddress;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentAddressController extends Controller
{
    public function index(): JsonResponse
    {
        $addresses = PaymentAddress::with('method')
            ->orderBy('payment_method_id')
            ->orderBy('sort')
            ->get();

        return response()->json($addresses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'label' => 'required|string|max:255',
            'meta' => 'required|array',
            'is_active' => 'boolean',
            'sort' => 'integer|min:0',
        ]);

        $address = PaymentAddress::create($validated);
        $address->load('method');

        return response()->json($address, 201);
    }

    public function update(Request $request, PaymentAddress $paymentAddress): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'meta' => 'required|array',
            'is_active' => 'boolean',
            'sort' => 'integer|min:0',
        ]);

        $paymentAddress->update($validated);
        $paymentAddress->load('method');

        return response()->json($paymentAddress);
    }

    public function destroy(PaymentAddress $paymentAddress): JsonResponse
    {
        $paymentAddress->delete();

        return response()->json(['message' => 'Address deleted']);
    }
}
