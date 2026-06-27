<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$router = $app->make(Illuminate\Routing\Router::class);

echo "API Middleware Group:\n";
print_r($router->getMiddlewareGroups()['api'] ?? []);

echo "\nWeb Middleware Group:\n";
print_r($router->getMiddlewareGroups()['web'] ?? []);

echo "\nSanctum Stateful:\n";
print_r(config('sanctum.stateful'));

echo "\nApp URL:\n";
echo config('app.url') . "\n";

echo "\nCurrent App URL with port:\n";
echo Laravel\Sanctum\Sanctum::currentApplicationUrlWithPort() . "\n";

echo "\nSession Domain:\n";
echo config('session.domain') . "\n";

echo "\nSession Cookie:\n";
echo config('session.cookie') . "\n";

echo "\nRequest Host:\n";
echo request()->getHost() . "\n";
