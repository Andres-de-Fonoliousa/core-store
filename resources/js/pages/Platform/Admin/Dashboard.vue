<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import {
  Building2, Users, Package, DollarSign,
  TrendingUp, AlertTriangle, CreditCard,
} from 'lucide-vue-next';

const api = useApi();
const stats = ref<any>(null);
const recentTenants = ref<any[]>([]);
const planBreakdown = ref<any>({});
const revenueByMonth = ref<any[]>([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const { data } = await api.get('/platform/admin/dashboard');
    stats.value = data.stats;
    recentTenants.value = data.recent_tenants;
    planBreakdown.value = data.plan_breakdown;
    revenueByMonth.value = data.revenue_by_month;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
});

const statCards = [
  { key: 'total_tenants', label: 'Total Tenants', icon: Building2, color: 'text-blue-400', bg: 'bg-blue-500/10' },
  { key: 'active_tenants', label: 'Active Tenants', icon: TrendingUp, color: 'text-emerald-400', bg: 'bg-emerald-500/10' },
  { key: 'suspended_tenants', label: 'Suspended', icon: AlertTriangle, color: 'text-red-400', bg: 'bg-red-500/10' },
  { key: 'total_users', label: 'Total Users', icon: Users, color: 'text-purple-400', bg: 'bg-purple-500/10' },
  { key: 'total_orders', label: 'Total Orders', icon: Package, color: 'text-orange-400', bg: 'bg-orange-500/10' },
  { key: 'total_revenue', label: 'Subscription Revenue', icon: DollarSign, color: 'text-green-400', bg: 'bg-green-500/10' },
  { key: 'platform_balance', label: 'Platform Balance', icon: CreditCard, color: 'text-indigo-400', bg: 'bg-indigo-500/10' },
];
</script>

<template>
  <Head title="Platform Admin Dashboard" />
  <div>
    <h1 class="text-2xl font-bold mb-6">Platform Dashboard</h1>

    <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>

    <template v-else-if="stats">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
        <div v-for="card in statCards" :key="card.key"
          class="rounded-xl border border-gray-800 bg-gray-900/50 p-5"
        >
          <div class="flex items-center justify-between mb-3">
            <div :class="`p-2 rounded-lg ${card.bg}`">
              <component :is="card.icon" :class="`w-5 h-5 ${card.color}`" />
            </div>
          </div>
          <p class="text-2xl font-bold text-white">{{ stats[card.key] ?? 0 }}</p>
          <p class="text-sm text-gray-400 mt-1">{{ card.label }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-xl border border-gray-800 bg-gray-900/50 p-5">
          <h2 class="text-lg font-semibold mb-4">Plan Breakdown</h2>
          <div class="space-y-3">
            <div v-for="(count, plan) in planBreakdown" :key="plan"
              class="flex items-center justify-between"
            >
              <span class="capitalize text-gray-300">{{ plan }}</span>
              <span class="text-white font-semibold">{{ count }}</span>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-gray-800 bg-gray-900/50 p-5">
          <h2 class="text-lg font-semibold mb-4">Revenue by Month</h2>
          <div v-if="revenueByMonth.length" class="space-y-2">
            <div v-for="row in revenueByMonth" :key="row.month"
              class="flex items-center justify-between text-sm"
            >
              <span class="text-gray-400">{{ row.month }}</span>
              <span class="text-green-400 font-mono">${{ Number(row.total).toFixed(2) }}</span>
            </div>
          </div>
          <p v-else class="text-gray-500 text-sm">No revenue data yet</p>
        </div>
      </div>

      <div class="mt-6 rounded-xl border border-gray-800 bg-gray-900/50 p-5">
        <h2 class="text-lg font-semibold mb-4">Recent Tenants</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-gray-400 border-b border-gray-800">
                <th class="text-left py-2">Name</th>
                <th class="text-left py-2">Plan</th>
                <th class="text-left py-2">Status</th>
                <th class="text-left py-2">Users</th>
                <th class="text-left py-2">Created</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in recentTenants" :key="t.id" class="border-b border-gray-800/50 hover:bg-gray-800/30">
                <td class="py-2 text-white">{{ t.name }}</td>
                <td class="py-2 capitalize">{{ t.plan }}</td>
                <td class="py-2">
                  <span :class="t.status === 'active' ? 'text-emerald-400' : t.status === 'suspended' ? 'text-red-400' : 'text-yellow-400'">
                    {{ t.status }}
                  </span>
                </td>
                <td class="py-2 text-gray-300">{{ t.user_count }}</td>
                <td class="py-2 text-gray-400">{{ t.created_at }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>
