<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShamCashProxyController extends Controller
{
    public function handle(Request $request, string $path = '')
    {
        // Debug endpoint — shows bridge state, reachable via HTTP
        if ($path === '_debug') {
            return $this->debug();
        }

        // Ensure trailing slash for root path
        if ($path === '' && !str_ends_with($request->getPathInfo(), '/')) {
            return redirect('/admin/sham-cash/');
        }

        $bridgeUrl = config('services.sham_cash.bridge_url', 'http://127.0.0.1:3001');
        $target = rtrim($bridgeUrl, '/') . '/' . ltrim($path, '/');

        // SSE streaming
        if (str_ends_with($path, 'api/events')) {
            return $this->streamSse($target);
        }

        if ($request->isMethod('GET')) {
            $response = Http::timeout(30)->get($target, $request->query());
        } else {
            $method = strtolower($request->method());
            $response = Http::timeout(30)->send($method, $target, [
                'json' => $request->json()->all() ?: $request->all(),
                'query' => $request->query(),
            ]);
        }

        $contentType = $response->header('Content-Type', 'text/html; charset=utf-8');
        $headers = ['Content-Type' => $contentType, 'Cache-Control' => 'no-cache, private'];

        return response($response->body(), $response->status(), $headers);
    }

    private function debug()
    {
        $bridgeUrl = config('services.sham_cash.bridge_url', 'http://127.0.0.1:3001');
        $state = ['bridge_url' => $bridgeUrl, 'time' => now()->toIso8601String()];

        try {
            $health = Http::timeout(5)->get(rtrim($bridgeUrl, '/') . '/health');
            $state['bridge_health'] = $health->json();
        } catch (\Exception $e) {
            $state['bridge_health'] = ['error' => $e->getMessage()];
        }

        try {
            $session = Http::timeout(5)->get(rtrim($bridgeUrl, '/') . '/api/session');
            $state['session'] = $session->json();
        } catch (\Exception $e) {
            $state['session'] = ['error' => $e->getMessage()];
        }

        try {
            $status = Http::timeout(5)->get(rtrim($bridgeUrl, '/') . '/api/status');
            $state['status'] = $status->json();
        } catch (\Exception $e) {
            $state['status'] = ['error' => $e->getMessage()];
        }

        return response()->json($state);
    }

    private function streamSse(string $target): StreamedResponse
    {
        return new StreamedResponse(function () use ($target) {
            $ctx = stream_context_create(['http' => [
                'method' => 'GET',
                'timeout' => 86400,
                'header' => "Accept: text/event-stream\r\n",
            ]]);
            $stream = fopen($target, 'r', false, $ctx);
            if (!$stream) return;
            // Disable output buffering for SSE
            if (function_exists('apache_setenv')) {
                apache_setenv('no-gzip', '1');
            }
            ini_set('zlib.output_compression', '0');
            ini_set('output_buffering', '0');
            ini_set('implicit_flush', '1');
            ob_implicit_flush(true);
            while (ob_get_level() > 0) ob_end_flush();
            while (!feof($stream)) {
                echo fgets($stream);
                ob_flush();
                flush();
                if (connection_aborted()) break;
            }
            fclose($stream);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
