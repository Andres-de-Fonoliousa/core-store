<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\Provider;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProviderBalanceLow extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Provider $provider,
        public readonly Product $product,
        public readonly float $requiredAmount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'provider_id'      => $this->provider->id,
            'provider_name'    => $this->provider->name,
            'provider_balance' => $this->provider->balance,
            'product_name'     => $this->product->name,
            'required_amount'  => $this->requiredAmount,
            'message'          => "'{$this->provider->name}' balance ({$this->provider->balance}) insufficient for '{$this->product->name}'. Required: {$this->requiredAmount}.",
        ];
    }
}
