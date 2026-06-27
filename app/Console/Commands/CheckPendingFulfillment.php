<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\FulfillmentService;
use Illuminate\Console\Command;

class CheckPendingFulfillment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:check-pending-fulfillment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update pending fulfillment orders';

    /**
     * Execute the console command.
     */
    public function handle(FulfillmentService $fulfillmentService): int
    {
        $pendingOrders = Order::where('fulfillment_status', 'pending_fulfillment')
            ->with(['product.provider'])
            ->get();

        if ($pendingOrders->isEmpty()) {
            $this->info('No pending fulfillment orders to check.');
            return Command::SUCCESS;
        }

        $this->info("Checking {$pendingOrders->count()} pending fulfillment orders...");

        $fulfilled = 0;
        $failed = 0;
        $stillPending = 0;

        foreach ($pendingOrders as $order) {
            try {
                $serial = $fulfillmentService->checkPendingOrder($order);

                if ($serial) {
                    $order->update([
                        'serial_code' => $serial,
                        'fulfillment_status' => 'fulfilled',
                    ]);
                    $fulfilled++;
                    $this->info("Order #{$order->id} fulfilled.");
                } else {
                    $stillPending++;
                    $this->line("Order #{$order->id} still pending.");
                }
            } catch (\Exception $e) {
                $order->update([
                    'fulfillment_status' => 'failed',
                    'fail_reason'        => $e->getMessage(),
                ]);
                $failed++;
                $this->error("Order #{$order->id} failed: {$e->getMessage()}");
            }
        }

        $this->info("Completed: {$fulfilled} fulfilled, {$failed} failed, {$stillPending} still pending.");

        return Command::SUCCESS;
    }
}
