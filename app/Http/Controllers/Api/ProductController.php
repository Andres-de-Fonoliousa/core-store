<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->where('status', 'active')
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->provider_id, fn($q) => $q->where('provider_id', $request->provider_id))
            ->when($request->categories, fn($q, $names) => $q->whereHas('category', fn($cq) => $cq->whereIn('name', explode(',', $names))))
            ->with(['category', 'provider:id,name,image'])
            ->paginate($request->integer('per_page', 20));

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'provider:id,name,image']);
        return response()->json($product);
    }
}