<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import { useUiStore } from '@/stores/uiStore';
import { Line, Bar, Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS, CategoryScale, LinearScale, PointElement,
  LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler,
} from 'chart.js';
import {
  Wallet, Package, Tags, Truck, TrendingUp, TrendingDown,
  Activity, BarChart3, PieChart, Zap, ChevronRight,
  ArrowUpRight, Users, RefreshCw,
} from 'lucide-vue-next';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler);

const api = useApi();
const toast = useToast();
const ui = useUiStore();

const pendingDeposits = ref(0);
const totalRevenue = ref(0);
const activeProducts = ref(0);
const totalOrders = ref(0);
const totalUsers = ref(0);
const loading = ref(false);

const kpiData = ref<any>(null);

const chartScales = {
  x: { ticks: { color: 'rgba(255,255,255,0.3)', font: { family: 'JetBrains Mono', size: 10 } }, grid: { color: 'rgba(255,255,255,0.05)' } },
  y: { beginAtZero: true, ticks: { color: 'rgba(255,255,255,0.3)', font: { family: 'JetBrains Mono', size: 10 } }, grid: { color: 'rgba(255,255,255,0.05)' } },
};

const revenueChartData = computed(() => ({
  labels: kpiData.value?.revenue?.labels ?? [],
  datasets: [{
    label: 'Revenue',
    backgroundColor: 'rgba(34, 211, 238, 0.15)',
    borderColor: 'rgb(34, 211, 238)',
    pointBackgroundColor: 'rgb(34, 211, 238)',
    pointBorderColor: '#050507',
    data: kpiData.value?.revenue?.data ?? [],
    fill: true,
    tension: 0.4,
    borderWidth: 2,
  }],
}));

const revenueChartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: chartScales };

const ordersByStatusData = computed(() => {
  const raw = kpiData.value?.orders_by_status ?? {};
  const labels = Object.keys(raw);
  const data = Object.values(raw) as number[];
  return { labels, datasets: [{ backgroundColor: ['rgba(34,197,94,0.85)', 'rgba(234,179,8,0.85)', 'rgba(239,68,68,0.85)', 'rgba(99,102,241,0.85)'], borderColor: ['rgb(34,197,94)', 'rgb(234,179,8)', 'rgb(239,68,68)', 'rgb(99,102,241)'], borderWidth: 1, borderRadius: 6, data }] };
});

const ordersByStatusOptions = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom' as const, labels: { color: 'rgba(255,255,255,0.6)', font: { family: 'JetBrains Mono', size: 11 }, padding: 20 } } },
  scales: chartScales,
};

const depositPieOptions = {
  responsive: true, maintainAspectRatio: false, cutout: '70%',
  plugins: { legend: { position: 'bottom' as const, labels: { color: 'rgba(255,255,255,0.6)', font: { family: 'JetBrains Mono', size: 11 }, padding: 20 } } },
};

ui.setPageTitle('Dashboard');

async function loadMetrics() {
  loading.value = true;
  try {
    const { data } = await api.get('/admin/kpi');
    kpiData.value = data;
    pendingDeposits.value = data.summary?.pending_deposits ?? 0;
    totalOrders.value = data.summary?.total_orders ?? 0;
    totalRevenue.value = data.summary?.total_revenue ?? 0;
    activeProducts.value = data.summary?.active_products ?? 0;
    totalUsers.value = data.summary?.total_users ?? 0;
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    loading.value = false;
  }
}

onMounted(loadMetrics);
</script>

<template>
  <Head title="Dashboard" />

  <div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">Overview</span>
        <h1 class="text-3xl font-bold tracking-tight mt-1">Dashboard</h1>
        <p class="text-sm text-white/40 mt-1">Real-time marketplace command center.</p>
      </div>
      <button @click="loadMetrics" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all active:scale-95 self-start">
        <RefreshCw class="w-3.5 h-3.5" /> Refresh
      </button>
    </div>

    <!-- Stats -->
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div class="group relative bg-white/[0.02] border border-white/5 rounded-2xl p-6 hover:border-cyan-400/20 hover:-translate-y-0.5 transition-all">
        <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-cyan-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
        <div class="relative flex items-start justify-between">
          <div>
            <p class="text-[11px] font-mono text-white/40 uppercase tracking-wider">Total Revenue</p>
            <p class="text-2xl font-bold text-white mt-2 font-mono tracking-tight">${{ totalRevenue.toFixed(2) }}</p>
            <div class="flex items-center gap-1 mt-2 text-emerald-400 text-[11px] font-mono"><TrendingUp class="w-3 h-3" /> +12% vs last month</div>
          </div>
          <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center"><Wallet class="w-5 h-5 text-cyan-400" /></div>
        </div>
      </div>

      <div class="group relative bg-white/[0.02] border border-white/5 rounded-2xl p-6 hover:border-amber-400/20 hover:-translate-y-0.5 transition-all">
        <div class="relative flex items-start justify-between">
          <div>
            <p class="text-[11px] font-mono text-white/40 uppercase tracking-wider">Pending Deposits</p>
            <p class="text-2xl font-bold text-white mt-2 font-mono tracking-tight">{{ pendingDeposits }}</p>
            <div class="flex items-center gap-1 mt-2 text-amber-400 text-[11px] font-mono"><TrendingDown class="w-3 h-3" /> -5% vs last week</div>
          </div>
          <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-400/20 flex items-center justify-center"><Package class="w-5 h-5 text-amber-400" /></div>
        </div>
      </div>

      <div class="group relative bg-white/[0.02] border border-white/5 rounded-2xl p-6 hover:border-emerald-400/20 hover:-translate-y-0.5 transition-all">
        <div class="relative flex items-start justify-between">
          <div>
            <p class="text-[11px] font-mono text-white/40 uppercase tracking-wider">Active Products</p>
            <p class="text-2xl font-bold text-white mt-2 font-mono tracking-tight">{{ activeProducts }}</p>
            <div class="flex items-center gap-1 mt-2 text-emerald-400 text-[11px] font-mono"><TrendingUp class="w-3 h-3" /> +8% new listings</div>
          </div>
          <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center"><Tags class="w-5 h-5 text-emerald-400" /></div>
        </div>
      </div>

      <div class="group relative bg-white/[0.02] border border-white/5 rounded-2xl p-6 hover:border-purple-400/20 hover:-translate-y-0.5 transition-all">
        <div class="relative flex items-start justify-between">
          <div>
            <p class="text-[11px] font-mono text-white/40 uppercase tracking-wider">Total Orders</p>
            <p class="text-2xl font-bold text-white mt-2 font-mono tracking-tight">{{ totalOrders }}</p>
            <div class="flex items-center gap-1 mt-2 text-emerald-400 text-[11px] font-mono"><TrendingUp class="w-3 h-3" /> +3% vs yesterday</div>
          </div>
          <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-400/20 flex items-center justify-center"><Truck class="w-5 h-5 text-purple-400" /></div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="grid gap-6 lg:grid-cols-2">
      <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-white flex items-center gap-2"><Activity class="w-4 h-4 text-cyan-400" /> Revenue (30 Days)</h3>
          <span class="text-[10px] font-mono text-white/30 bg-white/5 px-2 py-1 rounded border border-white/5">LIVE</span>
        </div>
        <div class="h-64"><Line v-if="!loading" :data="revenueChartData" :options="revenueChartOptions" /><div v-else class="flex h-full items-center justify-center text-white/30 font-mono text-sm"><Zap class="w-4 h-4 mr-2 animate-pulse" /> Loading...</div></div>
      </div>

      <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-white flex items-center gap-2"><BarChart3 class="w-4 h-4 text-emerald-400" /> Orders by Status</h3>
        </div>
        <div class="h-64"><Bar v-if="!loading" :data="ordersByStatusData" :options="ordersByStatusOptions" /><div v-else class="flex h-full items-center justify-center text-white/30 font-mono text-sm"><Zap class="w-4 h-4 mr-2 animate-pulse" /> Loading...</div></div>
      </div>

      <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-white flex items-center gap-2"><PieChart class="w-4 h-4 text-purple-400" /> Deposit Distribution</h3>
        </div>
        <div class="h-64"><Doughnut v-if="!loading" :data="depositPieData" :options="depositPieOptions" /><div v-else class="flex h-full items-center justify-center text-white/30 font-mono text-sm"><Zap class="w-4 h-4 mr-2 animate-pulse" /> Loading...</div></div>
      </div>

      <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl flex flex-col">
        <h3 class="text-sm font-semibold text-white mb-1">Quick Actions</h3>
        <p class="text-xs text-white/40 mb-5">Manage your marketplace efficiently.</p>
        <div class="flex-1 flex flex-col gap-3">
          <Link href="/admin/deposits" class="group flex items-center justify-between px-5 py-4 bg-white/[0.02] border border-white/5 rounded-xl hover:border-cyan-400/30 hover:bg-cyan-500/5 transition-all">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center"><Wallet class="w-4 h-4 text-cyan-400" /></div>
              <div><p class="text-sm font-medium text-white group-hover:text-cyan-400 transition-colors">Review Deposits</p><p class="text-[10px] text-white/40 font-mono">{{ pendingDeposits }} awaiting</p></div>
            </div>
            <ChevronRight class="w-4 h-4 text-white/20 group-hover:text-cyan-400 transition-colors" />
          </Link>
          <Link href="/admin/products" class="group flex items-center justify-between px-5 py-4 bg-white/[0.02] border border-white/5 rounded-xl hover:border-emerald-400/30 hover:bg-emerald-500/5 transition-all">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center"><Tags class="w-4 h-4 text-emerald-400" /></div>
              <div><p class="text-sm font-medium text-white group-hover:text-emerald-400 transition-colors">Manage Products</p><p class="text-[10px] text-white/40 font-mono">{{ activeProducts }} active</p></div>
            </div>
            <ChevronRight class="w-4 h-4 text-white/20 group-hover:text-emerald-400 transition-colors" />
          </Link>
          <Link href="/admin/settings" class="group flex items-center justify-between px-5 py-4 bg-white/[0.02] border border-white/5 rounded-xl hover:border-amber-400/30 hover:bg-amber-500/5 transition-all">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-400/20 flex items-center justify-center"><Activity class="w-4 h-4 text-amber-400" /></div>
              <div><p class="text-sm font-medium text-white group-hover:text-amber-400 transition-colors">Profit Margin</p><p class="text-[10px] text-white/40 font-mono">Global pricing rules</p></div>
            </div>
            <ChevronRight class="w-4 h-4 text-white/20 group-hover:text-amber-400 transition-colors" />
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>
