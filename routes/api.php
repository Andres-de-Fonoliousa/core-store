<?php

use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\DepositController as AdminDepositController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\PaymentAddressController;
use App\Http\Controllers\Api\Admin\ProviderController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\TransactionController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\NotificationController as CustomerNotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\Auth\TelegramLinkController;
use App\Http\Controllers\Api\ShamCashWebhookController;
use App\Http\Controllers\Api\TransactionController as CustomerTransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (no auth required)
|--------------------------------------------------------------------------
*/
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('payment-methods', [PaymentMethodController::class, 'index']);
Route::get('payment-methods/{paymentMethod}/addresses', [PaymentMethodController::class, 'addresses']);

/*
|--------------------------------------------------------------------------
| Bot-Authenticated Routes (require X-API-Key)
|--------------------------------------------------------------------------
*/
Route::middleware('api.key')->group(function () {
    // Telegram link auth
    Route::post('auth/telegram/send-otp', [TelegramLinkController::class, 'sendOtp']);
    Route::post('auth/telegram/verify-otp', [TelegramLinkController::class, 'verifyOtp']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Customer Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', fn (Request $request) => response()->json(['user' => $request->user()]));

    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::post('orders/{order}/payment-proof', [OrderController::class, 'uploadProof']);

    Route::get('transactions', [CustomerTransactionController::class, 'index']);
    Route::get('deposits/{transaction}', [DepositController::class, 'show']);
    Route::post('deposits', [DepositController::class, 'store']);

    // Notifications
    Route::get('notifications', [CustomerNotificationController::class, 'index']);
    Route::get('notifications/unread-count', [CustomerNotificationController::class, 'unreadCount']);
    Route::post('notifications/{notification}/read', [CustomerNotificationController::class, 'markAsRead']);
    Route::post('notifications/read-all', [CustomerNotificationController::class, 'markAllAsRead']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'admin', 'throttle:tenant-api'])->prefix('admin')->group(function () {
    // Tenant invitation management
    Route::get('invitations', [\App\Http\Controllers\Api\Admin\TenantInvitationController::class, 'index']);
    Route::post('invitations', [\App\Http\Controllers\Api\Admin\TenantInvitationController::class, 'invite']);
    Route::post('invitations/{invitation}/resend', [\App\Http\Controllers\Api\Admin\TenantInvitationController::class, 'resend']);
    Route::delete('invitations/{invitation}', [\App\Http\Controllers\Api\Admin\TenantInvitationController::class, 'revoke']);
    // Dashboard metrics
    Route::get('users', [UserController::class, 'index']);
    Route::get('orders', [AdminOrderController::class, 'index']);
    Route::get('orders/{order}', [AdminOrderController::class, 'show']);
    Route::post('orders/{order}/retry', [AdminOrderController::class, 'retry']);
    Route::post('orders/{order}/cancel', [AdminOrderController::class, 'cancel']);
    Route::get('deposits', [AdminDepositController::class, 'index']);
    Route::post('deposits/{transaction}/approve', [AdminDepositController::class, 'approve']);
    Route::post('deposits/{transaction}/reject', [AdminDepositController::class, 'reject']);

    // Transactions
    Route::get('transactions', [TransactionController::class, 'index']);
    Route::post('transactions/{transaction}/approve', [TransactionController::class, 'approve']);

    // Catalog
    Route::apiResource('categories', CategoryController::class);
    Route::post('categories/{category}/image', [CategoryController::class, 'uploadImage']);
    Route::apiResource('products', AdminProductController::class);
    Route::apiResource('providers', ProviderController::class);
    Route::post('providers/{provider}/top-up', [ProviderController::class, 'topUp']);

    // Notifications
    Route::get('notifications', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'index']);
    Route::post('notifications/{notification}/read', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'markAsRead']);
    Route::post('notifications/read-all', [\App\Http\Controllers\Api\Admin\NotificationController::class, 'markAllAsRead']);

    // Analytics
    Route::get('kpi', [\App\Http\Controllers\Api\Admin\KpiController::class, 'index'])->name('admin.api.kpi');

    // Onboarding
    Route::get('onboarding', [\App\Http\Controllers\Api\Admin\OnboardingController::class, 'status']);
    Route::post('onboarding/dismiss', [\App\Http\Controllers\Api\Admin\OnboardingController::class, 'dismiss']);
    Route::post('onboarding/complete', [\App\Http\Controllers\Api\Admin\OnboardingController::class, 'complete']);

    // Settings
    Route::get('settings/profit-margin', [SettingController::class, 'getMargin']);
    Route::post('settings/profit-margin', [SettingController::class, 'updateMargin']);

    // Payment Addresses
    Route::apiResource('payment-addresses', PaymentAddressController::class)->except('show');
});

/*
|--------------------------------------------------------------------------
| Platform Admin API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'platform.admin', 'throttle:tenant-api'])->prefix('platform/admin')->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Platform\Admin\DashboardController::class, 'index']);
    Route::get('tenants', [\App\Http\Controllers\Platform\Admin\DashboardController::class, 'tenants']);
    Route::get('tenants/{id}', [\App\Http\Controllers\Platform\Admin\DashboardController::class, 'showTenant']);
    Route::post('tenants/{id}/suspend', [\App\Http\Controllers\Platform\Admin\DashboardController::class, 'suspendTenant']);
    Route::post('tenants/{id}/activate', [\App\Http\Controllers\Platform\Admin\DashboardController::class, 'activateTenant']);
    Route::post('tenants/{id}/impersonate', [\App\Http\Controllers\Platform\Admin\DashboardController::class, 'impersonate']);
});

/*
|--------------------------------------------------------------------------
| Sham Cash Webhook (internal from Node bridge)
|--------------------------------------------------------------------------
*/
Route::post('sham-cash/webhook', [ShamCashWebhookController::class, 'incoming']);