<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { useUiStore } from '@/stores/uiStore';
import EmptyState from '@/components/EmptyState.vue';
import {
  Package, RefreshCw, Search, X, Eye, ChevronLeft, ChevronRight,
  Filter, ArrowUpDown, AlertTriangle, Clock, CheckCircle, XCircle,
  Zap, User, DollarSign,
} from 'lucide-vue-next';
import SkeletonTable from '@/components/SkeletonTable.vue';

const api = useApi();
const toast = useToast();
const ui = useUiStore();

const orders = ref<any[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const statusFilter = ref<string>('all');
const page = ref(1);
const lastPage = ref(1);
const totalItems = ref(0);

const statusOptions = [
  { value: 'all', label: 'الكل', color: 'text-white/60' },
  { value: 'pending_payment', label: 'في انتظار الدفع', color: 'text-amber-400' },
  { value: 'paid', label: 'مدفوع', color: 'text-cyan-400' },
  { value: 'fulfilled', label: 'مكتمل', color: 'text-emerald-400' },
  { value: 'cancelled', label: 'ملغي', color: 'text-red-400' },
];

const statusMeta: Record<string, { color: string; bg: string; border: string; icon: any }> = {
  pending_payment: { color: 'text-amber-400', bg: 'bg-amber-500/10', border: 'border-amber-400/20', icon: Clock },
  paid: { color: 'text-cyan-400', bg: 'bg-cyan-500/10', border: 'border-cyan-400/20', icon: Zap },
  fulfilled: { color: 'text-emerald-400', bg: 'bg-emerald-500/10', border: 'border-emerald-400/20', icon: CheckCircle },
  cancelled: { color: 'text-red-400', bg: 'bg-red-500/10', border: 'border-red-400/20', icon: XCircle },
};

const filteredOrders = computed(() => {
  let result = orders.value;
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(o =>
      o.id?.toString().includes(q) ||
      o.user?.name?.toLowerCase().includes(q) ||
      o.user?.email?.toLowerCase().includes(q) ||
      o.product?.name?.toLowerCase().includes(q)
    );
  }
  if (statusFilter.value !== 'all') {
    result = result.filter(o => o.status === statusFilter.value);
  }
  return result;
});

async function loadOrders() {
  loading.value = true;
  try {
    const { data } = await api.get('/admin/orders', { params: { page: page.value } });
    orders.value = data.data ?? data ?? [];
    page.value = data.current_page ?? 1;
    lastPage.value = data.last_page ?? 1;
    totalItems.value = data.total ?? orders.value.length;
  } catch (error) {
    toast.error('فشل تحميل الطلبات');
  } finally {
    loading.value = false;
  }
}

function clearFilters() {
  searchQuery.value = '';
  statusFilter.value = 'all';
}

function goPage(p: number) {
  if (p < 1 || p > lastPage.value) return;
  page.value = p;
  loadOrders();
}

ui.setPageTitle('Orders');

onMounted(loadOrders);
</script>

<template>
  <Head title="Orders" />

  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">Operations</span>
        <h1 class="text-3xl font-bold tracking-tight mt-1">Orders</h1>
        <p class="text-sm text-white/40 mt-1">{{ totalItems }} order{{ totalItems !== 1 ? 's' : '' }} total</p>
      </div>
      <button @click="loadOrders" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all active:scale-95">
        <RefreshCw class="w-3.5 h-3.5" /> Refresh
      </button>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-3">
      <div class="relative flex-1">
        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
        <input v-model="searchQuery" type="text" placeholder="Search by ID, user, or product..." class="w-full h-11 pl-12 pr-4 bg-white/[0.02] border border-white/10 rounded-xl text-sm text-white placeholder:text-white/25 focus:outline-none focus:border-cyan-400/50 transition-all" />
        <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-red-400 transition-colors">
          <X class="w-3.5 h-3.5" />
        </button>
      </div>
      <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <button v-for="opt in statusOptions" :key="opt.value" @click="statusFilter = opt.value" class="flex-shrink-0 px-4 py-2.5 text-xs rounded-xl border transition-all active:scale-95" :class="statusFilter === opt.value ? 'bg-cyan-400/10 text-cyan-400 border-cyan-400/30' : 'bg-white/[0.02] text-white/50 border-white/5 hover:border-white/10 hover:text-white'">
          {{ opt.label }}
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden backdrop-blur-xl">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-white/5 text-left text-xs font-mono uppercase tracking-[0.12em] text-white/40">
            <tr>
              <th class="px-5 py-4">ID</th>
              <th class="px-5 py-4">Customer</th>
              <th class="px-5 py-4">Product</th>
              <th class="px-5 py-4">Qty</th>
              <th class="px-5 py-4">Total</th>
              <th class="px-5 py-4">Status</th>
              <th class="px-5 py-4">Fulfillment</th>
              <th class="px-5 py-4">Date</th>
              <th class="px-5 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="9" class="px-5 py-10">
                <SkeletonTable :rows="8" :cols="9" />
              </td>
            </tr>

            <tr v-for="order in filteredOrders" :key="order.id" class="border-t border-white/5 hover:bg-white/[0.03] transition-colors">
              <td class="px-5 py-4 font-mono text-white/70">#{{ order.id }}</td>
              <td class="px-5 py-4">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-white/5 border border-white/10 flex items-center justify-center">
                    <User class="w-3.5 h-3.5 text-white/50" />
                  </div>
                  <div>
                    <p class="text-white/80 text-sm">{{ order.user?.name ?? 'Unknown' }}</p>
                    <p class="text-white/30 text-xs font-mono">{{ order.user?.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-4">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 overflow-hidden flex-shrink-0">
                    <img :src="order.product?.image || '/placeholder-product.png'" class="w-full h-full object-cover" />
                  </div>
                  <span class="text-white/80 text-sm truncate max-w-[140px]">{{ order.product?.name ?? '—' }}</span>
                </div>
              </td>
              <td class="px-5 py-4 font-mono text-white/60">×{{ order.quantity }}</td>
              <td class="px-5 py-4 font-mono text-cyan-400 font-semibold">${{ Number(order.price_at_time_of_order * order.quantity).toFixed(2) }}</td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-mono font-semibold rounded border backdrop-blur-sm" :class="[statusMeta[order.status]?.bg, statusMeta[order.status]?.border, statusMeta[order.status]?.color]">
                  <component :is="statusMeta[order.status]?.icon" class="w-3 h-3" />
                  {{ statusOptions.find(s => s.value === order.status)?.label ?? order.status }}
                </span>
              </td>
              <td class="px-5 py-4">
                <span v-if="order.fulfillment_status === 'pending'" class="text-[10px] font-mono text-amber-400/80 flex items-center gap-1">
                  <Clock class="w-3 h-3" /> pending
                </span>
                <span v-else-if="order.fulfillment_status === 'completed'" class="text-[10px] font-mono text-emerald-400/80 flex items-center gap-1">
                  <CheckCircle class="w-3 h-3" /> completed
                </span>
                <span v-else-if="order.fulfillment_status === 'failed'" class="text-[10px] font-mono text-red-400/80 flex items-center gap-1">
                  <XCircle class="w-3 h-3" /> failed
                </span>
                <span v-else class="text-[10px] font-mono text-white/30">{{ order.fulfillment_status }}</span>
              </td>
              <td class="px-5 py-4 text-white/40 font-mono text-xs">{{ new Date(order.created_at).toLocaleDateString() }}</td>
              <td class="px-5 py-4 text-right">
                <Link :href="`/admin/orders/${order.id}`" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium bg-white/5 text-white/70 border border-white/10 rounded-lg hover:bg-cyan-400/10 hover:text-cyan-400 hover:border-cyan-400/30 transition-all">
                  <Eye class="w-3.5 h-3.5" /> View
                </Link>
              </td>
            </tr>

            <tr v-if="!loading && filteredOrders.length === 0">
              <td colspan="9" class="px-5 py-16 text-center">
                <EmptyState :icon="Package" title="No orders found" description="Try adjusting your filters" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="lastPage > 1" class="flex items-center justify-center gap-3">
      <button @click="goPage(page - 1)" :disabled="page <= 1" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm rounded-xl border border-white/10 bg-white/[0.02] hover:border-cyan-400/30 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
        <ChevronRight class="w-4 h-4" /> Previous
      </button>
      <div class="flex items-center gap-2">
        <button v-for="p in lastPage" :key="p" @click="goPage(p)" class="w-10 h-10 text-sm rounded-xl border transition-all" :class="page === p ? 'bg-cyan-400/10 text-cyan-400 border-cyan-400/30 font-bold' : 'bg-white/[0.02] text-white/60 border-white/10 hover:border-cyan-400/20'">{{ p }}</button>
      </div>
      <button @click="goPage(page + 1)" :disabled="page >= lastPage" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm rounded-xl border border-white/10 bg-white/[0.02] hover:border-cyan-400/30 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
        Next <ChevronLeft class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>