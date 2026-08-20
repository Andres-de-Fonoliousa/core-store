<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Tenant\CatalogCache;
use App\Services\Tenant\PlanFeatures;
use App\Services\Tenant\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function __construct(
        private CatalogCache $catalogCache,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $parentId = $request->parent_id ?? 'null';
        $status = $request->status ?? 'active';
        $page = $request->integer('page', 1);
        $perPage = $request->integer('per_page', 50);
        $cacheKey = $this->catalogCache->key($status, $parentId, $page, $perPage);

        $cached = Cache::remember($cacheKey, 300, function () use ($request, $parentId, $status, $page, $perPage) {
            $q = Category::query()
                ->when($status !== 'all', fn ($q) => $q->where('status', $status))
                ->when($request->filled('parent_id'), function ($q) use ($parentId) {
                    if ($parentId === 'null') {
                        $q->whereNull('parent_id');
                    } else {
                        $q->where('parent_id', (int) $parentId);
                    }
                })
                ->orderBy('name');

            $total = $q->count();
            $items = (clone $q)->withCount(['products', 'children'])
                ->forPage($page, $perPage)
                ->get()
                ->toArray();

            return ['items' => $items, 'total' => $total];
        });

        return response()->json([
            'data' => $cached['items'],
            'total' => $cached['total'],
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($cached['total'] / max($perPage, 1)),
        ]);
    }

    private function bustCategoryCache(): void
    {
        $this->catalogCache->bust();
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
    public function store(Request $request): JsonResponse
    {
        $tenant = app(TenantManager::class)->getCurrent();
        if ($tenant && ! PlanFeatures::canCreateCategory($tenant)) {
            return response()->json([
                'error' => 'Category limit reached for your plan. Upgrade to add more categories.',
            ], 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ]);

        $category = Category::create($data);

        $this->bustCategoryCache();

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json($category);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): JsonResponse
    {
        return response()->json(['message' => 'Not available for API'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:2048'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive'],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ]);

        $category->update($data);

        $this->bustCategoryCache();

        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        $this->bustCategoryCache();

        return response()->json(null, 204);
    }

    public function uploadImage(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $file = $request->file('image');
        $filename = $category->id.'.jpg';
        $file->storeAs('categories', $filename, 'public');

        $path = 'storage/categories/'.$filename;
        $category->update(['image' => $path]);

        $this->bustCategoryCache();

        return response()->json([
            'image' => asset($path),
            'path' => $path,
        ]);
    }
}
