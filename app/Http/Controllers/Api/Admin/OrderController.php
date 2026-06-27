<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['product' => fn ($q) => $q->withTrashed(), 'product.category', 'user', 'transaction'])
            ->when($request->boolean('trashed'), fn ($q) => $q->withTrashed())
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['product' => fn ($q) => $q->withTrashed(), 'product.category' => fn ($q) => $q->withTrashed(), 'user', 'transaction']);

        return response()->json(['data' => $order]);
    }

    public function retry(Order $order, FulfillmentService $fulfillment): JsonResponse
    {
        if ($order->fulfillment_status !== 'failed') {
            return response()->json(['error' => 'Can only retry failed orders.'], 422);
        }

        try {
            $code = $fulfillment->dispatch($order->product, $order);
            $order->update([
                'serial_code'        => $code,
                'status'             => 'fulfilled',
                'fulfillment_status' => 'completed',
                'fail_reason'        => null,
            ]);

            return response()->json(['message' => 'Order fulfilled successfully.']);
        } catch (\Exception $e) {
            $order->update([
                'fulfillment_status' => 'failed',
                'fail_reason'        => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function cancel(Order $order): JsonResponse
    {
        if (!in_array($order->status, ['pending_payment', 'paid'])) {
            return response()->json(['error' => 'Order cannot be cancelled.'], 422);
        }

        $order->update([
            'status'             => 'cancelled',
            'fulfillment_status' => 'cancelled',
        ]);

        return response()->json(['message' => 'Order cancelled.']);
    }
}
