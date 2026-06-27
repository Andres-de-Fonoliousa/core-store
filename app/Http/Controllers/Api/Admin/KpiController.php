<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KpiController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Cache::remember('admin:kpi', 300, function () {
            $days = 30;
            $since = CarbonImmutable::now()->subDays($days - 1)->startOfDay();

            $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            $revenueDaily = Order::where('status', 'fulfilled')
                ->where('created_at', '>=', $since)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COALESCE(SUM(price_at_time_of_order), 0) as total'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->pluck('total', 'date');

            $ordersDaily = Order::where('created_at', '>=', $since)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->pluck('count', 'date');

            $usersDaily = User::where('created_at', '>=', $since)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->pluck('count', 'date');

            $depositsDaily = Transaction::where('type', 'deposit')
                ->where('status', 'approved')
                ->where('created_at', '>=', $since)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COALESCE(SUM(amount), 0) as total'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date')
                ->pluck('total', 'date');

            $topProducts = Order::where('status', 'fulfilled')
                ->select('product_id', DB::raw('count(*) as count'))
                ->groupBy('product_id')
                ->orderByDesc(DB::raw('count(*)'))
                ->take(10)
                ->with('product:id,name,price')
                ->get()
                ->map(fn ($o) => [
                    'name' => $o->product?->name ?? '(deleted)',
                    'price' => $o->product?->price ?? 0,
                    'orders_count' => $o->count,
                ]);

            $dateLabels = [];
            $revenueData = [];
            $ordersData = [];
            $usersData = [];
            $depositsData = [];

            for ($i = 0; $i < $days; $i++) {
                $date = $since->addDays($i)->toDateString();
                $dateLabels[] = $since->addDays($i)->format('M d');
                $revenueData[] = (float) ($revenueDaily[$date] ?? 0);
                $ordersData[] = (int) ($ordersDaily[$date] ?? 0);
                $usersData[] = (int) ($usersDaily[$date] ?? 0);
                $depositsData[] = (float) ($depositsDaily[$date] ?? 0);
            }

            $summary = [
                'total_revenue' => (float) Order::where('status', 'fulfilled')->sum('price_at_time_of_order'),
                'total_orders' => Order::count(),
                'active_products' => Product::where('status', 'active')->count(),
                'total_users' => User::count(),
                'pending_deposits' => Transaction::where('type', 'deposit')->where('status', 'pending')->count(),
                'pending_orders' => Order::where('status', 'paid')->count(),
                'failed_orders' => Order::whereIn('fulfillment_status', ['failed', 'cancelled'])->count(),
            ];

            return [
                'summary' => $summary,
                'revenue' => ['labels' => $dateLabels, 'data' => $revenueData],
                'orders' => ['labels' => $dateLabels, 'data' => $ordersData],
                'users' => ['labels' => $dateLabels, 'data' => $usersData],
                'deposits' => ['labels' => $dateLabels, 'data' => $depositsData],
                'orders_by_status' => $ordersByStatus,
                'top_products' => $topProducts,
            ];
        }));
    }
}
