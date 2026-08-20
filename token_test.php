<?php
require '/var/www/store-app/vendor/autoload.php';
$app = require_once '/var/www/store-app/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(1);
echo "User: " . $user->name . "\n";
echo "Token count: " . $user->tokens()->count() . "\n";
foreach ($user->tokens as $t) {
    echo "  name=$t->name id=$t->id\n";
}

$user->tokens()->where('name', 'web')->delete();
echo "After delete: " . $user->tokens()->count() . "\n";

$token = $user->createToken('web')->plainTextToken;
echo "New token: " . substr($token, 0, 30) . "...\n";
echo "Final count: " . $user->tokens()->count() . "\n";
