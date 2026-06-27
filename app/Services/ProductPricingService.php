<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;

class ProductPricingService
{
    public function updatePrice(Product $product): void
    {
        $marginPercent = Setting::where('key', 'profit_margin_percent')->value('value') ?? 0;
        $product->price = $product->cost_price * (1 + $marginPercent / 100);
        $product->save();
    }

    public function updateAllProductPrices(): void
    {
        $marginPercent = (float) (Setting::where('key', 'profit_margin_percent')->value('value') ?? 0);
        
        Product::query()->update([
            'price' => \DB::raw("cost_price * (1 + {$marginPercent} / 100)")
        ]);
    }
}
