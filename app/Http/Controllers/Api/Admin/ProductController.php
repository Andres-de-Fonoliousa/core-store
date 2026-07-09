<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductPricingService;
use App\Services\Tenant\PlanFeatures;
use App\Services\Tenant\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['provider', 'category'])
            ->when($request->boolean('trashed'), fn ($q) => $q->withTrashed())
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('provider_id'), fn ($query) => $query->where('provider_id', $request->provider_id))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->category_id))
            ->when($request->filled('is_auto'), fn ($query) => $query->where('is_auto', $request->boolean('is_auto')))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%' . $request->search . '%'))
            ->orderBy('name')
            ->paginate(20);

        return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): JsonResponse
    {
        return response()->json(['message' => 'Not available for API'], 404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ProductPricingService $pricingService): JsonResponse
    {
        $tenant = app(TenantManager::class)->getCurrent();
        if ($tenant && !PlanFeatures::canCreateProduct($tenant)) {
            return response()->json([
                'error' => 'Product limit reached for your plan. Upgrade to add more products.',
            ], 403);
        }

        $data = $request->validate([
            'provider_id' => ['required', 'exists:providers,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'external_id' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:2048'],
            'params' => ['nullable', 'array'],
            'qty_values' => ['required', 'array'],
            'is_auto' => ['boolean'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'apply_profit_margin' => ['boolean'],
        ]);

        $product = Product::create($data);

        // Auto-calculate price from cost_price using global profit margin
        if ($request->boolean('apply_profit_margin')) {
            $pricingService->updatePrice($product);
        }

        Cache::increment('cat_idx_v');

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load(['provider', 'category']);

        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): JsonResponse
    {
        return response()->json(['message' => 'Not available for API'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product, ProductPricingService $pricingService): JsonResponse
    {
        $data = $request->validate([
            'provider_id' => ['sometimes', 'required', 'exists:providers,id'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost_price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'external_id' => ['sometimes', 'required', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:2048'],
            'params' => ['nullable', 'array'],
            'qty_values' => ['sometimes', 'required', 'array'],
            'is_auto' => ['boolean'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive'],
            'apply_profit_margin' => ['boolean'],
        ]);

        $product->update($data);

        // Auto-calculate price from cost_price using global profit margin
        if ($request->boolean('apply_profit_margin')) {
            $pricingService->updatePrice($product);
        }

        Cache::increment('cat_idx_v');

        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        Cache::increment('cat_idx_v');

        return response()->json(null, 204);
    }
}
