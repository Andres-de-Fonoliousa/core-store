<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { useUiStore } from '@/stores/uiStore';
import { Line, Bar, Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS, CategoryScale, LinearScale, PointElement,
  LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler,
} from 'chart.js';
import {
  TrendingUp, ShoppingBag, Package, Users, DollarSign,
  Activity, RefreshCw, ArrowUpRight, Clock, AlertTriangle,
} from 'lucide-vue-next';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler);

const api = useApi();
const toast = useToast();
const ui = useUiStore();

const loading = ref(true);
const data = ref<any>(null);

const chartScales = {
  x: { ticks: { color: 'rgba(255,255,255,0.3)', font: { family: 'JetBrains Mono', size: 10 } }, grid: { color: 'rgba(255,255,255,0.05)' } },
  y: { beginAtZero: true, ticks: { color: 'rgba(255,255,255,0.3)', font: { family: 'JetBrains Mono', size: 10 } }, grid: { color: 'rgba(255,255,255,0.05)' } },
};

const chartOptions = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { labels: { color: 'rgba(255,255,255,0.5)', font: { family: 'JetBrains Mono', size: 10 } } } },
  scales: chartScales,
};

const revenueChart = computed(() => data.value ? {
  labels: data.value.revenue.labels,
  datasets: [{
    label: 'Revenue',
    backgroundColor: 'rgba(34,211,238,0.15)',
    borderColor: 'rgb(34,211,238)',
    pointBackgroundColor: 'rgb(34,211,238)',
    pointBorderColor: '#050507',
    data: data.value.revenue.data,
    fill: true,
    tension: 0.4,
    borderWidth: 2,
  }],
} : null);

const ordersChart = computed(() => data.value ? {
  labels: data.value.orders.labels,
  datasets: [{
    label: 'Orders',
    backgroundColor: 'rgba(139,92,246,0.6)',
    borderColor: 'rgb(139,92,246)',
    borderWidth: 1,
    borderRadius: 4,
    data: data.value.orders.data,
  }],
} : null);

const usersChart = computed(() => data.value ? {
  labels: data.value.users.labels,
  datasets: [{
    label: 'New Users',
    backgroundColor: 'rgba(16,185,129,0.6)',
    borderColor: 'rgb(16,185,129)',
    borderWidth: 1,
    borderRadius: 4,
    data: data.value.users.data,
  }],
} : null);

const depositsChart = computed(() => data.value ? {
  labels: data.value.deposits.labels,
  datasets: [{
    label: 'Deposits',
    backgroundColor: 'rgba(245,158,11,0.15)',
    borderColor: 'rgb(245,158,11)',
    pointBackgroundColor: 'rgb(245,158,11)',
    pointBorderColor: '#050507',
    data: data.value.deposits.data,
    fill: true,
    tension: 0.4,
    borderWidth: 2,
  }],
} : null);

const ordersByStatusChart = computed(() => {
  if (!data.value?.orders_by_status) return null;
  const statusMap: Record<string, string> = {
    pending: 'rgba(234,179,8,0.85)',
    paid: 'rgba(59,130,246,0.85)',
    fulfilled: 'rgba(34,197,94,0.85)',
    cancelled: 'rgba(239,68,68,0.85)',
    failed: 'rgba(107,114,128,0.85)',
  };
  const labels = Object.keys(data.value.orders_by_status);
  const colors = labels.map(l => statusMap[l] ?? 'rgba(255,255,255,0.3)');
  return {
    labels,
    datasets: [{
      backgroundColor: colors,
      borderColor: colors.map(c => c.replace('0.85', '1')),
      borderWidth: 1,
      data: Object.values(data.value.orders_by_status),
    }],
  };
});

const revenueChartOptions = { ...chartOptions, plugins: { ...chartOptions.plugins, legend: { display: false } } };
const ordersByStatusOptions = {
  responsive: true, maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom' as const, labels: { color: 'rgba(255,255,255,0.5)', font: { family: 'JetBrains Mono', size: 11 }, padding: 16 } },
  },
};

ui.setPageTitle('Analytics');

async function loadKpi() {
  loading.value = true;
  try {
    const res = await api.get('/admin/kpi');
    data.value = res.data;
  } catch (error) {
    toast.error('Failed to load analytics');
  } finally {
    loading.value = false;
  }
}

onMounted(loadKpi);
</script>

<template>
  <Head title="Analytics" />

  <div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">KPI</span>
        <h1 class="text-3xl font-bold tracking-tight mt-1">Analytics</h1>
        <p class="text-sm text-white/40 mt-1">Business metrics and trends over the last 30 days.</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="/pulse" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all active:scale-95">
          <Activity class="w-3.5 h-3.5" /> Pulse
        </a>
        <button @click="loadKpi" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all active:scale-95">
          <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" /> Refresh
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-32">
      <div class="flex flex-col items-center gap-4">
        <Activity class="w-8 h-8 text-cyan-400 animate-pulse" />
        <span class="text-sm text-white/30 font-mono">Loading analytics...</span>
      </div>
    </div>

    <template v-if="data && !loading">
      <!-- Summary Cards -->
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="group relative bg-white/[0.02] border border-white/5 rounded-2xl p-6 hover:border-cyan-400/20 hover:-translate-y-0.5 transition-all">
          <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-cyan-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
          <div class="relative flex items-start justify-between">
            <div>
              <p class="text-xs font-mono text-white/40 tracking-wide">Total Revenue</p>
              <p class="text-2xl font-bold mt-2 tracking-tight">{{ data.summary.total_revenue.toFixed(2) }}</p>
              <p class="text-[11px] text-white/30 mt-1 font-mono">Lifetime</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center">
              <DollarSign class="w-5 h-5 text-emerald-400" />
            </div>
          </div>
        </div>

        <div class="group relative bg-white/[0.02] border border-white/5 rounded-2xl p-6 hover:border-purple-400/20 hover:-translate-y-0.5 transition-all">
          <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-purple-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
          <div class="relative flex items-start justify-between">
            <div>
              <p class="text-xs font-mono text-white/40 tracking-wide">Total Orders</p>
              <p class="text-2xl font-bold mt-2 tracking-tight">{{ data.summary.total_orders }}</p>
              <p class="text-[11px] text-white/30 mt-1 font-mono">{{ data.summary.pending_orders }} pending</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-400/20 flex items-center justify-center">
              <ShoppingBag class="w-5 h-5 text-purple-400" />
            </div>
          </div>
        </div>

        <div class="group relative bg-white/[0.02] border border-white/5 rounded-2xl p-6 hover:border-cyan-400/20 hover:-translate-y-0.5 transition-all">
          <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-cyan-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
          <div class="relative flex items-start justify-between">
            <div>
              <p class="text-xs font-mono text-white/40 tracking-wide">Active Products</p>
              <p class="text-2xl font-bold mt-2 tracking-tight">{{ data.summary.active_products }}</p>
              <p class="text-[11px] text-white/30 mt-1 font-mono">In catalog</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center">
              <Package class="w-5 h-5 text-cyan-400" />
            </div>
          </div>
        </div>

        <div class="group relative bg-white/[0.02] border border-white/5 rounded-2xl p-6 hover:border-emerald-400/20 hover:-translate-y-0.5 transition-all">
          <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
          <div class="relative flex items-start justify-between">
            <div>
              <p class="text-xs font-mono text-white/40 tracking-wide">Total Users</p>
              <p class="text-2xl font-bold mt-2 tracking-tight">{{ data.summary.total_users }}</p>
              <p class="text-[11px] text-white/30 mt-1 font-mono">{{ data.summary.pending_deposits }} pending deposits</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center">
              <Users class="w-5 h-5 text-emerald-400" />
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid gap-6 lg:grid-cols-2">
        <!-- Revenue Chart -->
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-sm font-semibold text-white">Revenue (30 days)</h3>
              <p class="text-[11px] text-white/30 font-mono mt-0.5">Daily fulfilled order revenue</p>
            </div>
            <TrendingUp class="w-5 h-5 text-cyan-400/60" />
          </div>
          <div class="h-64">
            <Line v-if="revenueChart" :data="revenueChart" :options="revenueChartOptions" />
          </div>
        </div>

        <!-- Orders Chart -->
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-sm font-semibold text-white">Orders (30 days)</h3>
              <p class="text-[11px] text-white/30 font-mono mt-0.5">Daily order volume</p>
            </div>
            <ShoppingBag class="w-5 h-5 text-purple-400/60" />
          </div>
          <div class="h-64">
            <Bar v-if="ordersChart" :data="ordersChart" :options="chartOptions" />
          </div>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <!-- New Users -->
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-sm font-semibold text-white">New Users (30 days)</h3>
              <p class="text-[11px] text-white/30 font-mono mt-0.5">Daily registrations</p>
            </div>
            <Users class="w-5 h-5 text-emerald-400/60" />
          </div>
          <div class="h-64">
            <Bar v-if="usersChart" :data="usersChart" :options="chartOptions" />
          </div>
        </div>

        <!-- Deposits -->
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-sm font-semibold text-white">Deposits (30 days)</h3>
              <p class="text-[11px] text-white/30 font-mono mt-0.5">Approved deposit volume</p>
            </div>
            <TrendingUp class="w-5 h-5 text-amber-400/60" />
          </div>
          <div class="h-64">
            <Line v-if="depositsChart" :data="depositsChart" :options="revenueChartOptions" />
          </div>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <!-- Orders by Status -->
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-sm font-semibold text-white">Orders by Status</h3>
              <p class="text-[11px] text-white/30 font-mono mt-0.5">Current distribution</p>
            </div>
            <Activity class="w-5 h-5 text-white/40" />
          </div>
          <div class="h-64 flex items-center justify-center">
            <div class="w-56">
              <Doughnut v-if="ordersByStatusChart" :data="ordersByStatusChart" :options="ordersByStatusOptions" />
            </div>
          </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-sm font-semibold text-white">Top Products</h3>
              <p class="text-[11px] text-white/30 font-mono mt-0.5">Best sellers (all time)</p>
            </div>
            <Package class="w-5 h-5 text-white/40" />
          </div>
          <div class="space-y-2">
            <div v-for="(p, i) in data.top_products" :key="i" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/[0.02] border border-white/5 hover:border-white/10 transition-colors">
              <span class="w-6 h-6 rounded-lg flex items-center justify-center text-[10px] font-bold font-mono" :class="i === 0 ? 'bg-amber-500/20 text-amber-400' : i === 1 ? 'bg-slate-400/20 text-slate-300' : i === 2 ? 'bg-orange-500/20 text-orange-400' : 'bg-white/5 text-white/30'">{{ i + 1 }}</span>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ p.name }}</p>
                <p class="text-[11px] font-mono text-white/30">{{ p.orders_count }} orders</p>
              </div>
              <span class="text-xs font-mono text-cyan-400">{{ Number(p.price).toFixed(2) }}</span>
            </div>
            <div v-if="!data.top_products?.length" class="text-center py-8 text-sm text-white/20 font-mono">
              No fulfilled orders yet
            </div>
          </div>
        </div>
      </div>

      <!-- Footer summary -->
      <div class="flex items-center justify-between px-4 py-3 rounded-xl bg-white/[0.01] border border-white/5">
        <div class="flex items-center gap-4 text-[11px] font-mono text-white/30">
          <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400/60" /> {{ data.summary.active_products }} active</span>
          <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400/60" /> {{ data.summary.pending_deposits }} pending deposits</span>
          <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-400/60" /> {{ data.summary.failed_orders }} failed orders</span>
        </div>
        <span class="text-[10px] font-mono text-white/20">Last 30 days</span>
      </div>
    </template>
  </div>
</template>
