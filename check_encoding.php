<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = App\Models\Category::find(164);
echo 'Raw hex: ' . bin2hex($c->name) . PHP_EOL;
echo 'UTF-8 chars: ' . PHP_EOL;
for ($i = 0; $i < strlen($c->name); $i++) {
    echo dechex(ord($c->name[$i])) . ' ';
}
echo PHP_EOL;
echo 'As JSON: ' . json_encode($c->name, JSON_UNESCAPED_UNICODE) . PHP_EOL;
echo PHP_EOL;
echo 'All categories:' . PHP_EOL;
foreach (App\Models\Category::all() as $cat) {
    echo "  [{$cat->id}] raw=" . bin2hex($cat->name) . " json=" . json_encode($cat->name, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
