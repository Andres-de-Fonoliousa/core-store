<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SitemapController;

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::inertia('/', 'Welcome')->name('home');
Route::inertia('/browse', 'Public/Home')->name('browse');
Route::get('browse/{category}', function (string $category) {
    $cat = \App\Models\Category::findOrFail((int) $category);
    $ancestors = [];
    $current = $cat;
    while ($current->parent) {
        $current = $current->parent;
        $ancestors[] = ['id' => $current->id, 'name' => $current->name];
    }
    return \Inertia\Inertia::render('Public/Home', [
        'categoryId'   => $cat->id,
        'categoryName' => $cat->name,
        'categoryAncestors' => array_reverse($ancestors),
        'categoryParentId' => $cat->parent_id,
    ]);
})->name('browse.category');
Route::inertia('/about', 'Public/About')->name('about');
Route::inertia('/terms', 'Public/Terms')->name('terms');
Route::inertia('/privacy', 'Public/Privacy')->name('privacy');
Route::inertia('/support', 'Public/Support')->name('support');
Route::get('products/{product}', fn (string $product) => Inertia::render('Public/ProductDetail', [
    'productId' => (int) $product,
]))->name('product.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Customer/Dashboard')->name('dashboard');
    Route::inertia('orders', 'Customer/Orders')->name('orders.index');
    Route::get('orders/{order}', fn (string $order) => Inertia::render('Customer/OrderDetail', [
        'id' => (int) $order,
    ]))->name('orders.show');
    Route::inertia('deposit', 'Customer/Deposit')->name('deposit.index');
    Route::get('deposit/{deposit}', fn (string $deposit) => Inertia::render('Customer/DepositShow', [
        'id' => (int) $deposit,
    ]))->name('deposit.show');
    Route::inertia('notifications', 'Customer/Notifications')->name('notifications.index');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::inertia('/', 'Admin/Dashboard')->name('admin.dashboard');
    Route::inertia('users', 'Admin/Users')->name('admin.users');
    Route::inertia('orders', 'Admin/Orders')->name('admin.orders');
    Route::get('orders/{order}', fn (string $order) => Inertia::render('Admin/OrderDetail', [
        'id' => (int) $order,
    ]))->name('admin.orders.show');
    Route::inertia('deposits', 'Admin/Deposits')->name('admin.deposits');
    Route::inertia('transactions', 'Admin/Transactions')->name('admin.transactions');
    Route::inertia('categories', 'Admin/Categories')->name('admin.categories');
    Route::inertia('providers', 'Admin/Providers')->name('admin.providers');
    Route::inertia('products', 'Admin/Products')->name('admin.products');
    Route::inertia('notifications', 'Admin/Notifications')->name('admin.notifications');
    Route::inertia('payment-addresses', 'Admin/PaymentAddresses')->name('admin.payment-addresses');
    Route::inertia('settings', 'Admin/Settings')->name('admin.settings');
    Route::inertia('kpi', 'Admin/Kpi')->name('admin.kpi');
    Route::any('sham-cash/{any?}', [\App\Http\Controllers\Admin\ShamCashProxyController::class, 'handle'])
        ->where('any', '.*')
        ->name('admin.sham-cash');
});

require __DIR__.'/settings.php';
