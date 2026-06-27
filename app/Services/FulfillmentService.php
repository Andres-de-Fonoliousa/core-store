<?php

namespace App\Services;

use App\Exceptions\InsufficientProviderBalanceException;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;

class FulfillmentService
{
    public function dispatch(Product $product, Order $order): string
    {
        $provider = $product->provider;

        $requiredAmount = $product->cost_price * $order->quantity;
        if ($provider->balance < $requiredAmount) {
            throw new InsufficientProviderBalanceException($provider);
        }

        $api = new World4CardService($provider);

        // Generate a new UUID for idempotency
        $uuid = (string) Str::uuid();

        // Gather extra params from order.details (which includes playerId and any other fields)
        $details = $order->details ?? [];
        $playerId = $details['playerId'] ?? 'default';
        $extraParams = $details['params'] ?? [];

        // Place the order
        $result = $api->createOrder(
            $product->external_id,   // the provider's product ID
            $order->quantity,
            $playerId,
            $uuid,
            $extraParams
        );

        $status = $result['status'] ?? null;
        $data = $result['data'] ?? [];

        // Save provider IDs
        $order->update([
            'order_uuid' => $uuid,
            'provider_order_id' => $data['order_id'] ?? null,
        ]);

        if ($status === 'accept') {
            // Extract code from replay_api
            $replayApi = $data['replay_api'] ?? [];
            $firstReplay = $replayApi[0]['replay'] ?? [];
            $code = $firstReplay[0] ?? null;

            if (!$code) {
                throw new \Exception('No serial code in provider response');
            }
            return $code;
        }

        if ($status === 'reject') {
            $message = $data['message'] ?? 'unknown';
            if (str_contains(strtolower($message), 'balance')) {
                throw new InsufficientProviderBalanceException($provider, $message);
            }
            throw new \Exception('Provider rejected the order: ' . $message);
        }

        // status 'wait' – we'll need to poll later; throw an exception so the controller
        // can set the order to 'pending_fulfillment' and a job can poll.
        throw new \Exception('Order is pending on provider, will retry later.');
    }

    public function checkPendingOrder(Order $order): ?string
    {
        if (!$order->provider_order_id && !$order->order_uuid) {
            return null;
        }

        $provider = $order->product->provider;
        $api = new World4CardService($provider);

        // Use provider_order_id if available, otherwise use order_uuid
        $orderIdToCheck = $order->provider_order_id ?: $order->order_uuid;
        $isUuid = !$order->provider_order_id;

        $result = $api->checkOrder($orderIdToCheck, $isUuid);
        $status = $result['status'] ?? null;
        $orderData = $result['data'][0] ?? null;

        if ($status === 'accept') {
            $replayApi = $orderData['replay_api'] ?? [];
            $firstReplay = $replayApi[0]['replay'] ?? [];
            $code = $firstReplay[0] ?? null;

            if (!$code) {
                throw new \Exception('No serial code in provider response');
            }

            return $code;
        }

        if ($status === 'reject') {
            throw new \Exception('Order rejected by provider: ' . ($orderData['message'] ?? 'unknown'));
        }

        return null; // still waiting
    }
}