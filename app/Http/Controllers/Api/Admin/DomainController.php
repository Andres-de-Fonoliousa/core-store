<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainAlias;
use App\Models\Tenant;
use App\Services\Tenant\DomainVerifier;
use App\Services\Tenant\PlanFeatures;
use App\Services\Tenant\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DomainController extends Controller
{
    public function __construct(
        private TenantManager $manager,
    ) {}

    public function index(): JsonResponse
    {
        $tenant = $this->manager->getCurrent();

        if (! $tenant) {
            return response()->json(['message' => 'No tenant context'], 404);
        }

        return response()->json($tenant->domainAliases()->orderBy('created_at')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $this->manager->getCurrent();

        if (! $tenant) {
            return response()->json(['message' => 'No tenant context'], 404);
        }

        if (! PlanFeatures::hasFeature($tenant, 'custom_domain')) {
            return response()->json([
                'message' => 'Custom domains are not available on your plan. Upgrade to use custom domains.',
            ], 403);
        }

        $data = $request->validate([
            'domain' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?\.[a-z]{2,}$/i',
                Rule::unique('domain_aliases', 'domain'),
            ],
        ]);

        if (Tenant::where('domain', $data['domain'])->exists()) {
            return response()->json(['message' => 'Domain is already in use'], 409);
        }

        $alias = $tenant->domainAliases()->create([
            'domain' => strtolower($data['domain']),
            'verification_token' => Str::random(48),
        ]);

        return response()->json([
            'message' => 'Domain added. Add the TXT record below, then verify it.',
            'domain' => $alias,
        ], 201);
    }

    public function verify(Request $request, DomainAlias $domainAlias, DomainVerifier $verifier): JsonResponse
    {
        $tenant = $this->manager->getCurrent();

        if (! $tenant || $domainAlias->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if ($domainAlias->verified_at) {
            return response()->json(['message' => 'Domain is already verified']);
        }

        if (! $verifier->verify($domainAlias)) {
            return response()->json([
                'message' => 'Verification failed. Add the TXT record store-app-verify='.$domainAlias->verification_token.' to your DNS and try again.',
            ], 422);
        }

        $domainAlias->update(['verified_at' => now()]);

        return response()->json(['message' => 'Domain verified', 'domain' => $domainAlias]);
    }

    public function destroy(Request $request, DomainAlias $domainAlias): JsonResponse
    {
        $tenant = $this->manager->getCurrent();

        if (! $tenant || $domainAlias->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $domainAlias->delete();

        return response()->json(['message' => 'Domain removed']);
    }
}
