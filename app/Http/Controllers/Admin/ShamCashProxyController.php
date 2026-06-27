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
            while (!feof($stream)) {
                echo fgets($stream);
                ob_flush();
                flush();
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
