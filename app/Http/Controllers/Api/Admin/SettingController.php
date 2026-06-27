<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ProductPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function updateMargin(Request $request, ProductPricingService $pricingService): JsonResponse
    {
        $request->validate(['percent' => 'required|numeric|min:0|max:100']);

        Setting::updateOrCreate(['key' => 'profit_margin_percent'], ['value' => $request->percent]);

        // Optionally recalculate all product prices
        if ($request->boolean('recalculate_all')) {
            $pricingService->updateAllProductPrices();
        }

        return response()->json(['percent' => $request->percent]);
    }

    public function getMargin(): JsonResponse
    {
        $percent = Setting::where('key', 'profit_margin_percent')->value('value') ?? 0;

        return response()->json(['percent' => (float) $percent]);
    }
}
