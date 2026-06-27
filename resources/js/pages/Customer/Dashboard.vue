<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import { useAuthStore } from '@/stores/authStore';
import BalanceCard from '@/components/BalanceCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import type { Order } from '@/types/models';
import {
  Wallet, ShoppingBag, Box, ArrowLeft, Zap,
  Clock, AlertTriangle, RefreshCw,
} from 'lucide-vue-next';

const api = useApi();
const toast = useToast();
const authStore = useAuthStore();

const recentOrders = ref<Order[]>([]);
const totalOrders = ref(0);
const totalSpent = ref(0);
const activeProducts = ref(0);
const loading = ref(false);

async function loadDashboard() {
  loading.value = true;
  try {
    const [ordersRes, productsRes] = await Promise.all([
      api.get('/orders'),
      api.get('/products', { params: { per_page: 1 } }),
    ]);
    recentOrders.value = ordersRes.data.data.slice(0, 5);
    totalOrders.value = ordersRes.data.total ?? ordersRes.data.data.length;
    totalSpent.value = ordersRes.data.data.reduce((sum: number, o: Order) => sum + Number(o.price_at_time_of_order), 0);
    activeProducts.value = productsRes.data.total ?? 0;
  } catch (err) {
    toast.error(parseApiError(err));
  } finally {
    loading.value = false;
  }
}

onMounted(loadDashboard);
</script>

<template>
  <Head :title="$t('nav.dashboard')" />

  <div class="space-y-8">
    <!-- Welcome -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">لوحة التحكم</span>
        <h1 class="text-3xl font-bold tracking-tight mt-1 text-white">مرحباً، {{ authStore.user?.name?.split(' ')[0] ?? 'Player' }}</h1>
        <p class="text-sm text-white/40 mt-1">نظرة عامة على نشاطك في المتجر.</p>
      </div>
      <Link href="/orders" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 transition-all active:scale-95 hover:shadow-[0_0_25px_-5px_rgba(34,211,238,0.5)]">
        <ShoppingBag class="w-4 h-4" /> {{ $t('nav.orders') }}
      </Link>
    </div>

    <!-- Stats + Balance -->
    <div class="grid gap-4 xl:grid-cols-[1.5fr_1fr]">
      <!-- Stats -->
      <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl space-y-6">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center">
            <Zap class="w-5 h-5 text-cyan-400" />
          </div>
          <div>
            <h2 class="text-lg font-semibold text-white">ملخص الحساب</h2>
            <p class="text-xs text-white/40 font-mono">آخر تحديث: الآن</p>
          </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
          <div class="bg-white/[0.02] border border-white/5 rounded-xl p-5 hover:border-cyan-400/20 transition-all">
            <p class="text-[11px] font-mono text-white/40 uppercase tracking-wider">إجمالي الإنفاق</p>
            <p class="mt-2 text-2xl font-bold text-white font-mono tracking-tight">${{ totalSpent.toFixed(2) }}</p>
          </div>
          <div class="bg-white/[0.02] border border-white/5 rounded-xl p-5 hover:border-purple-400/20 transition-all">
            <p class="text-[11px] font-mono text-white/40 uppercase tracking-wider">الطلبات</p>
            <p class="mt-2 text-2xl font-bold text-white font-mono tracking-tight">{{ totalOrders }}</p>
          </div>
          <div class="bg-white/[0.02] border border-white/5 rounded-xl p-5 hover:border-emerald-400/20 transition-all">
            <p class="text-[11px] font-mono text-white/40 uppercase tracking-wider">منتجات نشطة</p>
            <p class="mt-2 text-2xl font-bold text-white font-mono tracking-tight">{{ activeProducts }}</p>
          </div>
        </div>
      </div>

      <!-- Balance Card -->
      <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl flex flex-col justify-between">
        <BalanceCard />
        <div class="mt-6 flex flex-col gap-3">
          <Link href="/deposit" class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 transition-all active:scale-95">
            <Wallet class="w-4 h-4" /> {{ $t('nav.deposit') }}
          </Link>
          <Link href="/" class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold border border-white/10 text-white/70 rounded-xl hover:bg-white/5 hover:text-white hover:border-white/20 transition-all">
            <Box class="w-4 h-4" /> {{ $t('nav.catalog') }}
          </Link>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl space-y-6">
      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-400/20 flex items-center justify-center">
            <Clock class="w-5 h-5 text-purple-400" />
          </div>
          <div>
            <h2 class="text-lg font-semibold text-white">آخر الطلبات</h2>
            <p class="text-xs text-white/40 font-mono">آخر 5 عمليات شراء</p>
          </div>
        </div>
        <Link href="/orders" class="inline-flex items-center gap-2 text-sm font-medium text-cyan-400 hover:text-cyan-300 transition-colors group">
          عرض الكل <ArrowLeft class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
        </Link>
      </div>

      <div class="overflow-hidden rounded-2xl border border-white/5 bg-white/[0.02]">
        <table class="min-w-full text-sm">
          <thead class="bg-white/5 text-left text-xs font-mono uppercase tracking-[0.12em] text-white/40">
            <tr>
              <th class="px-5 py-4">الطلب</th>
              <th class="px-5 py-4">المنتج</th>
              <th class="px-5 py-4">المبلغ</th>
              <th class="px-5 py-4">الحالة</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="4" class="px-5 py-10 text-center text-white/30 font-mono text-sm">
                <div class="flex items-center justify-center gap-2">
                  <div class="w-4 h-4 border-2 border-cyan-400/30 border-t-cyan-400 rounded-full animate-spin" />
                  {{ $t('common.loading') }}
                </div>
              </td>
            </tr>
            <tr v-for="order in recentOrders" :key="order.id" class="border-t border-white/5 hover:bg-white/[0.03] transition-colors">
              <td class="px-5 py-4 font-mono text-white/70">#{{ order.id }}</td>
              <td class="px-5 py-4 text-white font-medium">{{ order.product?.name ?? '—' }}</td>
              <td class="px-5 py-4 font-mono text-cyan-400 font-semibold">${{ Number(order.price_at_time_of_order).toFixed(2) }}</td>
              <td class="px-5 py-4">
                <span class="rounded-full px-3 py-1 text-[11px] font-mono font-semibold border" :class="order.fulfillment_status === 'fulfilled' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-400/20' : order.fulfillment_status === 'pending' ? 'bg-amber-500/10 text-amber-400 border-amber-400/20' : 'bg-white/5 text-white/40 border-white/10'">
                  {{ order.fulfillment_status.replace(/_/g, ' ') }}
                </span>
              </td>
            </tr>
            <tr v-if="!loading && recentOrders.length === 0">
              <td colspan="4" class="px-5 py-16 text-center">
                <EmptyState :icon="ShoppingBag" title="لا توجد طلبات" description="ابدأ بتصفح المنتجات وإجراء أول عملية شراء." />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>