<?php

namespace App\Listeners;

use App\Models\Order;
use App\Services\Tenant\PlanFeatures;

class DeductTransactionFee
{
    public function handle(Order $order): void
    {
        $tenant = $order->tenant;

        if (!$tenant) {
            return;
        }

        $fee = PlanFeatures::transactionFee($tenant->plan);

        if ($fee <= 0) {
            return;
        }

        $amount = $order->price_at_time_of_order * $fee;

        $tenant->increment('platform_balance', $amount);
    }
}
