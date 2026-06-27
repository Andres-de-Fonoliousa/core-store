<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShamCashBridgeService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.sham_cash.bridge_url', 'http://127.0.0.1:3001');
    }

    public function checkTransaction(int|string $tranId, float $amount, int $currencyId = 1): array
    {
        try {
            $response = Http::timeout(15)->post("{$this->baseUrl}/check", [
                'tranId' => (string) $tranId,
                'amount' => $amount,
                'currencyId' => $currencyId,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('ShamCash bridge check failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['found' => false, 'error' => 'bridge_error'];
        } catch (\Exception $e) {
            Log::error('ShamCash bridge unreachable', [
                'message' => $e->getMessage(),
            ]);

            return ['found' => false, 'error' => 'bridge_unreachable'];
        }
    }

    public function fetchIncoming(\DateTime $since = null): array
    {
        try {
            $payload = [];
            if ($since) {
                $payload['since'] = $since->format('Y-m-d\TH:i:s');
            }

            $response = Http::timeout(30)->post("{$this->baseUrl}/incoming", $payload);

            if ($response->successful()) {
                return $response->json()['transactions'] ?? [];
            }

            Log::warning('ShamCash bridge incoming fetch failed', [
                'status' => $response->status(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('ShamCash bridge incoming fetch unreachable', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function health(): bool
    {
        try {
            return Http::timeout(5)->get("{$this->baseUrl}/health")->successful();
        } catch (\Exception) {
            return false;
        }
    }
}
