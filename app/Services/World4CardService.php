<?php

namespace App\Services;

use App\Models\Provider;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class World4CardService
{
    public function __construct(
        protected Provider $provider
    ) {}

    public function getProfile(): array
    {
        return $this->request('get', '/client/api/profile');
    }

    public function getProducts(?array $productIds = null): array
    {
        $url = '/client/api/products';
        if ($productIds) {
            $url .= '?products_id=' . implode(',', $productIds);
        }
        return $this->request('get', $url);
    }

    public function getContent(int $parentId = 0): array
    {
        return $this->request('get', "/client/api/content/{$parentId}");
    }

    public function createOrder(int $productId, int $qty, string $playerId, string $orderUuid, array $extraParams = []): array
    {
        $query = http_build_query(array_merge([
            'qty' => $qty,
            'playerId' => $playerId,
            'order_uuid' => $orderUuid,
        ], $extraParams));

        $url = "/client/api/newOrder/{$productId}/params?{$query}";
        return $this->request('get', $url);
    }

    public function checkOrder(string $orderIdOrUuid, bool $isUuid = false): array
    {
        $query = $isUuid ? 'uuid=1' : '';
        $url = "/client/api/check?orders=[{$orderIdOrUuid}]&{$query}";
        return $this->request('get', $url);
    }

    public function getContentMany(array $parentIds): array
    {
        $responses = Http::pool(fn (\Illuminate\Http\Client\Pool $pool) => array_map(
            fn(int $id) => $pool->acceptJson()
                ->withHeaders(['api-token' => $this->provider->token])
                ->baseUrl($this->provider->base_url)
                ->asMultipart()
                ->get("/client/api/content/{$id}"),
            $parentIds
        ));

        $results = [];
        foreach ($responses as $i => $response) {
            $id = $parentIds[$i];
            if ($response instanceof \Illuminate\Http\Client\Response) {
                if ($response->successful()) {
                    $results[$id] = $response->json() ?? [];
                } else {
                    $results[$id] = [];
                }
            } else {
                $results[$id] = [];
            }
        }
        return $results;
    }

    protected function request(string $method, string $uri): array
    {
        $response = Http::timeout(60)
            ->acceptJson()
            ->withHeaders(['api-token' => $this->provider->token])
            ->baseUrl($this->provider->base_url)
            ->$method($uri);

        $this->handleError($response);
        $body = $response->json();
        if ($body === null) {
            throw new \Exception('Provider returned empty or non-JSON response: ' . $response->body());
        }
        return $body;
    }

    protected function handleError(Response $response): void
    {
        if ($response->failed()) {
            $code = $response->json('code') ?? $response->status();
            throw new \Exception("Provider API error: {$code} - " . $response->body());
        }
        // Check for API error codes inside a 200 response
        $body = $response->json();
        if (isset($body['status']) && $body['status'] === 'error') {
            throw new \Exception("Provider API error: {$body['message']}");
        }
    }
}
