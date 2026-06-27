<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientProviderBalanceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\ProviderBalanceLow;
use App\Notifications\UserNotification;
use App\Services\FulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::with(['product.category', 'transaction'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    public function store(StoreOrderRequest $request, FulfillmentService $fulfillment): JsonResponse
    {
        $user = auth()->user();
        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity;
        $totalPrice = $product->price * $quantity;

        if ($user->balance < $totalPrice) {
            return response()->json(['error' => 'Insufficient balance'], 422);
        }

        // Build details from request
        $details = [
            'playerId' => $request->playerId,
            'params'   => $request->params,
        ];

        try {
            $order = DB::transaction(function () use ($user, $product, $totalPrice, $quantity, $details, $fulfillment) {
                // Deduct balance
                $before = $user->balance;
                $user->decrement('balance', $totalPrice);
                $after = $user->fresh()->balance;

                // Create transaction
                $transaction = Transaction::create([
                    'user_id'        => $user->id,
                    'amount'         => -$totalPrice,
                    'type'           => 'purchase',
                    'status'         => 'completed',
                    'balance_before' => $before,
                    'balance_after'  => $after,
                    'date'           => now(),
                ]);

                // Create order
                $order = Order::create([
                    'user_id'                => $user->id,
                    'product_id'             => $product->id,
                    'price_at_time_of_order' => $totalPrice,
                    'quantity'               => $quantity,
                    'status'                 => 'paid',
                    'transaction_id'         => $transaction->id,
                    'fulfillment_status'     => 'pending',
                    'details'                => $details,
                ]);

                // Link order to transaction
                $transaction->update(['order_id' => $order->id]);

                // Auto‑fulfill if possible
                if ($product->is_auto) {
                    try {
                        $serial = $fulfillment->dispatch($product, $order);
                        $order->update([
                            'serial_code'        => $serial,
                            'fulfillment_status' => 'fulfilled',
                        ]);
                    } catch (InsufficientProviderBalanceException $e) {
                        $order->update([
                            'fulfillment_status' => 'failed',
                            'fail_reason'        => 'out of stock',
                        ]);
                        throw $e;
                    } catch (\Exception $e) {
                        $reason = $e->getMessage();
                        if (str_contains($reason, 'pending on provider')) {
                            $order->update(['fulfillment_status' => 'pending_fulfillment']);
                        } else {
                            $order->update([
                                'fulfillment_status' => 'failed',
                                'fail_reason'        => $reason,
                            ]);
                        }
                    }
                }

                return $order;
            });
            $order->user->notify(new UserNotification(
                title: 'تم تأكيد الطلب',
                body: "طلب {$product->name} × {$quantity} بقيمة \${$totalPrice} قيد التوصيل.",
                url: '/orders',
            ));

            if ($order->fulfillment_status === 'fulfilled') {
                $order->user->notify(new UserNotification(
                    title: 'تم توصيل الطلب',
                    body: "تم توصيل {$product->name} × {$quantity} بنجاح.",
                    url: '/orders',
                ));
            } elseif ($order->fulfillment_status === 'failed') {
                $order->user->notify(new UserNotification(
                    title: 'فشل توصيل الطلب',
                    body: "تعذر توصيل {$product->name}. سيتم إعادة المبلغ إلى محفظتك.",
                    url: '/orders',
                ));
            }
        } catch (InsufficientProviderBalanceException $e) {
            User::where('role', 'admin')->get()->each->notify(
                new ProviderBalanceLow($e->provider, $product, $product->cost_price * $quantity)
            );
            return response()->json(['error' => 'out of stock'], 422);
        }

        return response()->json($order->load('transaction'), 201);
    }

    public function uploadProof(): JsonResponse
    {
        return response()->json(['message' => 'Use POST /api/deposits instead'], 501);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);
        $order->load(['product' => fn ($q) => $q->withTrashed(), 'product.provider' => fn ($q) => $q->withTrashed(), 'product.category' => fn ($q) => $q->withTrashed(), 'transaction']);
        // serial_code auto-decrypted thanks to cast
        return response()->json($order);
    }
}