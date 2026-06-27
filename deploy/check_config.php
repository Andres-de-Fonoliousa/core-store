<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class);

echo "Stateful: " . implode(', ', config('sanctum.stateful') ?? ['empty']) . "\n";
echo "App URL: " . config('app.url') . "\n";
echo "Sanctum::currentApplicationUrlWithPort: " . Laravel\Sanctum\Sanctum::currentApplicationUrlWithPort() . "\n";
echo "Session domain: " . (config('session.domain') ?? 'null') . "\n";
echo "Session cookie: " . config('session.cookie') . "\n";
echo "Env SANCTUM_STATEFUL_DOMAINS: " . (env('SANCTUM_STATEFUL_DOMAINS') ?? 'NOT SET') . "\n";
