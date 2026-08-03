<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionInvoice;
use App\Services\Tenant\PlanFeatures;
use App\Services\Tenant\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function __construct(
        private TenantManager $manager,
    ) {}

    public function overview(): JsonResponse
    {
        $tenant = $this->manager->getCurrent();

        if (! $tenant) {
            return response()->json(['message' => 'No tenant context'], 404);
        }

        $latestInvoice = $tenant->invoices()->latest('period_end')->first();

        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'plan' => $tenant->plan,
                'status' => $tenant->status,
                'platform_balance' => (float) $tenant->platform_balance,
                'trial_ends_at' => $tenant->trial_ends_at,
                'subscribed_at' => $tenant->subscribed_at,
                'expires_at' => $tenant->expires_at,
            ],
            'plan' => [
                'price_monthly' => PlanFeatures::price($tenant->plan),
                'transaction_fee' => PlanFeatures::transactionFee($tenant->plan),
                'limits' => PlanFeatures::limits($tenant->plan),
                'features' => PlanFeatures::features($tenant->plan),
            ],
            'usage' => [
                'products' => $tenant->products()->count(),
                'categories' => $tenant->categories()->count(),
                'users' => $tenant->users()->count(),
            ],
            'next_billing_date' => $latestInvoice?->period_end ?? $tenant->expires_at,
        ]);
    }

    public function invoices(): JsonResponse
    {
        $tenant = $this->manager->getCurrent();

        if (! $tenant) {
            return response()->json(['message' => 'No tenant context'], 404);
        }

        return response()->json(
            SubscriptionInvoice::where('tenant_id', $tenant->id)
                ->latest('period_start')
                ->paginate(20)
        );
    }

    public function changePlan(Request $request): JsonResponse
    {
        $tenant = $this->manager->getCurrent();

        if (! $tenant) {
            return response()->json(['message' => 'No tenant context'], 404);
        }

        $data = $request->validate([
            'plan' => ['required', 'string', Rule::in(PlanFeatures::plans())],
        ]);

        $previous = $tenant->plan;
        $tenant->update(['plan' => $data['plan']]);

        return response()->json([
            'message' => "Plan changed from {$previous} to {$data['plan']}",
            'plan' => PlanFeatures::plans(),
        ]);
    }
}
