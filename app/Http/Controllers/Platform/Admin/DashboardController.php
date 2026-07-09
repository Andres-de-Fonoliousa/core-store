<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\Order;
use App\Models\SubscriptionInvoice;
use App\Services\Tenant\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::whereIn('status', ['active', 'trial'])->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();
        $totalUsers = TenantUser::whereNotNull('joined_at')->count();
        $totalOrders = Order::count();
        $totalRevenue = SubscriptionInvoice::where('status', 'paid')->sum('amount');
        $platformBalance = Tenant::sum('platform_balance');

        $recentTenants = Tenant::latest()->take(10)->get()->map(fn ($t) => [
            'id' => $t->id,
            'uuid' => $t->uuid,
            'name' => $t->name,
            'plan' => $t->plan,
            'status' => $t->status,
            'user_count' => $t->users()->count(),
            'created_at' => $t->created_at,
        ]);

        $planBreakdown = Tenant::select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->pluck('count', 'plan');

        $revenueByMonth = SubscriptionInvoice::where('status', 'paid')
            ->select(DB::raw("strftime('%Y-%m', paid_at) as month"), DB::raw('sum(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'stats' => [
                'total_tenants' => $totalTenants,
                'active_tenants' => $activeTenants,
                'suspended_tenants' => $suspendedTenants,
                'total_users' => $totalUsers,
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'platform_balance' => $platformBalance,
            ],
            'recent_tenants' => $recentTenants,
            'plan_breakdown' => $planBreakdown,
            'revenue_by_month' => $revenueByMonth,
        ]);
    }

    public function tenants(Request $request): JsonResponse
    {
        $tenants = Tenant::query()
            ->withCount('users')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('plan'), fn ($q) => $q->where('plan', $request->plan))
            ->orderBy($request->get('sort', 'created_at'), $request->get('dir', 'desc'))
            ->paginate(20);

        return response()->json($tenants);
    }

    public function showTenant(int $id): JsonResponse
    {
        $tenant = Tenant::withCount('users', 'products', 'orders')->findOrFail($id);

        $recentOrders = $tenant->orders()->with('user')->latest()->take(10)->get();
        $recentUsers = $tenant->users()->latest()->take(10)->get();
        $invoices = SubscriptionInvoice::where('tenant_id', $id)->latest()->get();

        return response()->json([
            'tenant' => $tenant,
            'recent_orders' => $recentOrders,
            'recent_users' => $recentUsers,
            'invoices' => $invoices,
        ]);
    }

    public function suspendTenant(int $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => 'suspended']);

        return response()->json(['message' => 'Tenant suspended']);
    }

    public function activateTenant(int $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => 'active']);

        return response()->json(['message' => 'Tenant activated']);
    }

    public function impersonate(int $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        $adminUser = request()->user();

        $targetUser = $tenant->users()->wherePivot('role', 'owner')->first()
            ?? $tenant->users()->first();

        if (!$targetUser) {
            return response()->json(['message' => 'No user found for this tenant'], 404);
        }

        $token = $targetUser->createToken('impersonation-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $targetUser,
            'tenant' => $tenant,
            'impersonator' => $adminUser->id,
        ]);
    }
}
