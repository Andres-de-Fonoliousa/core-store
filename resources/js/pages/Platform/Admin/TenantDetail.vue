<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { ArrowLeft } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

const page = usePage();
const api = useApi();
const toast = useToast();
const tenantId = (page.props as any).tenantId as number;

const tenant = ref<any>(null);
const recentOrders = ref<any[]>([]);
const recentUsers = ref<any[]>([]);
const invoices = ref<any[]>([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const { data } = await api.get(`/platform/admin/tenants/${tenantId}`);
    tenant.value = data.tenant;
    recentOrders.value = data.recent_orders;
    recentUsers.value = data.recent_users;
    invoices.value = data.invoices;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
});

async function suspend() {
  try {
    await api.post(`/platform/admin/tenants/${tenantId}/suspend`);
    toast.success('Tenant suspended');
    tenant.value.status = 'suspended';
  } catch (e: any) {
    toast.error(e?.response?.data?.message ?? 'Failed');
  }
}

async function activate() {
  try {
    await api.post(`/platform/admin/tenants/${tenantId}/activate`);
    toast.success('Tenant activated');
    tenant.value.status = 'active';
  } catch (e: any) {
    toast.error(e?.response?.data?.message ?? 'Failed');
  }
}
</script>

<template>
  <Head :title="tenant?.name ?? 'Tenant'" />
  <div>
    <Link :href="route('platform.admin.tenants')" class="inline-flex items-center gap-1 text-sm text-indigo-400 hover:text-indigo-300 mb-4">
      <ArrowLeft class="w-4 h-4" />
      Back to Tenants
    </Link>

    <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>

    <template v-else-if="tenant">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold">{{ tenant.name }}</h1>
          <p class="text-sm text-gray-400 mt-1">UUID: {{ tenant.uuid }}</p>
        </div>
        <div class="flex gap-2">
          <button v-if="tenant.status !== 'suspended'" @click="suspend"
            class="px-4 py-2 text-sm rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 transition-colors"
          >
            Suspend
          </button>
          <button v-else @click="activate"
            class="px-4 py-2 text-sm rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20 transition-colors"
          >
            Activate
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="rounded-xl border border-gray-800 bg-gray-900/50 p-5">
          <h2 class="text-sm text-gray-400 mb-1">Plan</h2>
          <p class="text-lg font-semibold capitalize">{{ tenant.plan }}</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900/50 p-5">
          <h2 class="text-sm text-gray-400 mb-1">Status</h2>
          <p :class="tenant.status === 'active' ? 'text-emerald-400' : tenant.status === 'suspended' ? 'text-red-400' : 'text-yellow-400'"
            class="text-lg font-semibold capitalize"
          >{{ tenant.status }}</p>
        </div>
        <div class="rounded-xl border border-gray-800 bg-gray-900/50 p-5">
          <h2 class="text-sm text-gray-400 mb-1">Balance</h2>
          <p class="text-lg font-semibold">${{ Number(tenant.platform_balance).toFixed(2) }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-xl border border-gray-800 bg-gray-900/50 p-5">
          <h2 class="text-lg font-semibold mb-4">Recent Orders</h2>
          <div v-if="recentOrders.length" class="space-y-2">
            <div v-for="o in recentOrders" :key="o.id" class="flex justify-between text-sm py-1">
              <span class="text-gray-300">Order #{{ o.id }}</span>
              <span class="text-green-400 font-mono">${{ Number(o.total_price).toFixed(2) }}</span>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">No orders</p>
        </div>

        <div class="rounded-xl border border-gray-800 bg-gray-900/50 p-5">
          <h2 class="text-lg font-semibold mb-4">Recent Users</h2>
          <div v-if="recentUsers.length" class="space-y-2">
            <div v-for="u in recentUsers" :key="u.id" class="flex justify-between text-sm py-1">
              <span class="text-gray-300">{{ u.name }}</span>
              <span class="text-gray-400">{{ u.email }}</span>
            </div>
          </div>
          <p v-else class="text-sm text-gray-500">No users</p>
        </div>
      </div>

      <div class="mt-6 rounded-xl border border-gray-800 bg-gray-900/50 p-5">
        <h2 class="text-lg font-semibold mb-4">Invoices</h2>
        <div v-if="invoices.length" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-gray-400 border-b border-gray-800">
                <th class="text-left py-2">Period</th>
                <th class="text-left py-2">Plan</th>
                <th class="text-left py-2">Amount</th>
                <th class="text-left py-2">Status</th>
                <th class="text-left py-2">Paid At</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="inv in invoices" :key="inv.id" class="border-b border-gray-800/50">
                <td class="py-2 text-gray-300">{{ inv.period_start }} - {{ inv.period_end }}</td>
                <td class="py-2 capitalize">{{ inv.plan_code }}</td>
                <td class="py-2 text-green-400 font-mono">${{ Number(inv.amount).toFixed(2) }}</td>
                <td class="py-2">
                  <span :class="inv.status === 'paid' ? 'text-emerald-400' : 'text-red-400'">{{ inv.status }}</span>
                </td>
                <td class="py-2 text-gray-400">{{ inv.paid_at ?? '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-else class="text-sm text-gray-500">No invoices</p>
      </div>
    </template>
  </div>
</template>
