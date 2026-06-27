<?php
// Test script - simulates a stateful API request
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "Sanctum Stateful:\n";
print_r(config('sanctum.stateful'));

echo "\nApp URL:\n";
echo config('app.url') . "\n";

echo "\nSession Cookie Domain:\n";
echo config('session.domain') . "\n";

echo "\nSession Cookie Name:\n";
echo config('session.cookie') . "\n";

echo "\nGuard configured:\n";
print_r(config('sanctum.guard'));

echo "\n\nAll done\n";
