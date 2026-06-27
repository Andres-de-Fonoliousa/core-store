<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $providers = Provider::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('sync_active'), fn ($query) => $query->where('sync_active', $request->boolean('sync_active')))
            ->orderBy('name')
            ->paginate(20);

        return response()->json($providers->through(fn ($p) => $p->makeVisible('token')));
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
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'string', 'url', 'max:2048'],
            'token' => ['required', 'string', 'max:2048'],
            'image' => ['nullable', 'string', 'max:2048'],
            'sync_active' => ['boolean'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'balance' => ['numeric', 'min:0'],
        ]);

        $provider = Provider::create($data);

        return response()->json($provider->makeVisible('token'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Provider $provider): JsonResponse
    {
        return response()->json($provider->makeVisible('token'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Provider $provider): JsonResponse
    {
        return response()->json(['message' => 'Not available for API'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Provider $provider): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'base_url' => ['sometimes', 'required', 'string', 'url', 'max:2048'],
            'token' => ['sometimes', 'required', 'string', 'max:2048'],
            'image' => ['nullable', 'string', 'max:2048'],
            'sync_active' => ['boolean'],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive'],
            'balance' => ['numeric', 'min:0'],
        ]);

        $provider->update($data);

        return response()->json($provider->makeVisible('token'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Provider $provider): JsonResponse
    {
        $provider->delete();

        return response()->json(null, 204);
    }

    public function topUp(): JsonResponse
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }
}
