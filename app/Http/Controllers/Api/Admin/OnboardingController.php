<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\Tenant\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function __construct(
        private TenantManager $manager,
    ) {}

    public function status(): JsonResponse
    {
        $tenant = $this->manager->getCurrent();
        if (!$tenant) {
            return response()->json(['onboarded' => true]);
        }

        return response()->json([
            'onboarded' => $tenant->onboarding_completed_at !== null,
            'seen' => $tenant->seen_onboarding_at !== null,
            'completed_at' => $tenant->onboarding_completed_at,
        ]);
    }

    public function dismiss(): JsonResponse
    {
        $tenant = $this->manager->getCurrent();
        if (!$tenant) {
            return response()->json(['message' => 'No tenant'], 404);
        }

        $tenant->update(['seen_onboarding_at' => now()]);

        return response()->json(['message' => 'Onboarding dismissed']);
    }

    public function complete(Request $request): JsonResponse
    {
        $tenant = $this->manager->getCurrent();
        if (!$tenant) {
            return response()->json(['message' => 'No tenant'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'logo_url' => 'sometimes|nullable|string|max:2048',
            'brand_color' => 'sometimes|nullable|string|max:7',
            'currency' => 'sometimes|nullable|string|max:3',
        ]);

        $tenant->update([
            ...$data,
            'onboarding_completed_at' => now(),
            'seen_onboarding_at' => now(),
        ]);

        return response()->json(['message' => 'Onboarding completed']);
    }
}
