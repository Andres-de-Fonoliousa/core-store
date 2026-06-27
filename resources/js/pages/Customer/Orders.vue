<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import OrderStatusBadge from '@/components/OrderStatusBadge.vue';
import EmptyState from '@/components/EmptyState.vue';
import type { Order } from '@/types/models';
import {
  ShoppingBag, Wallet, ArrowLeft, RefreshCw,
  AlertTriangle,
} from 'lucide-vue-next';

const api = useApi();
const toast = useToast();
const orders = ref<Order[]>([]);
const loading = ref(false);

async function loadOrders() {
  loading.value = true;
  try {
    const { data } = await api.get('/orders');
    orders.value = data.data;
  } catch (err) {
    toast.error(parseApiError(err));
  } finally {
    loading.value = false;
  }
}

onMounted(loadOrders);
</script>

<template>
  <Head :title="$t('nav.orders')" />

  <div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">المشتريات</span>
        <h1 class="text-3xl font-bold tracking-tight mt-1 text-white">{{ $t('nav.orders') }}</h1>
        <p class="text-sm text-white/40 mt-1">تتبع حالة مشترياتك ومراجعات التوصيل.</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="loadOrders" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all active:scale-95">
          <RefreshCw class="w-3.5 h-3.5" /> تحديث
        </button>
        <Link href="/deposit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 transition-all active:scale-95 hover:shadow-[0_0_25px_-5px_rgba(34,211,238,0.5)]">
          <Wallet class="w-4 h-4" /> {{ $t('nav.deposit') }}
        </Link>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden backdrop-blur-xl">
      <table class="min-w-full text-sm">
        <thead class="bg-white/5 text-left text-xs font-mono uppercase tracking-[0.12em] text-white/40">
          <tr>
            <th class="px-5 py-4">الطلب</th>
            <th class="px-5 py-4">المنتج</th>
            <th class="px-5 py-4">الكمية</th>
            <th class="px-5 py-4">المبلغ</th>
            <th class="px-5 py-4">الحالة</th>
            <th class="px-5 py-4">التوصيل</th>
            <th class="px-5 py-4">التاريخ</th>
            <th class="px-5 py-4"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="8" class="px-5 py-10 text-center text-white/30 font-mono text-sm">
              <div class="flex items-center justify-center gap-2">
                <div class="w-4 h-4 border-2 border-cyan-400/30 border-t-cyan-400 rounded-full animate-spin" />
                {{ $t('common.loading') }}
              </div>
            </td>
          </tr>
          <tr v-for="order in orders" :key="order.id" class="border-t border-white/5 hover:bg-white/[0.03] transition-colors">
            <td class="px-5 py-4 font-mono text-white/70">#{{ order.id }}</td>
            <td class="px-5 py-4 text-white font-medium">{{ order.product?.name ?? '—' }}</td>
            <td class="px-5 py-4 font-mono text-white/60">{{ order.quantity }}</td>
            <td class="px-5 py-4 font-mono text-cyan-400 font-semibold">${{ Number(order.price_at_time_of_order).toFixed(2) }}</td>
            <td class="px-5 py-4"><OrderStatusBadge :status="order.status" type="order" /></td>
            <td class="px-5 py-4"><OrderStatusBadge :status="order.fulfillment_status" type="fulfillment" /></td>
            <td class="px-5 py-4 text-white/40 font-mono text-xs">{{ new Date(order.created_at).toLocaleDateString() }}</td>
            <td class="px-5 py-4 text-right">
              <Link :href="`/orders/${order.id}`" class="inline-flex items-center gap-1.5 text-sm font-medium text-cyan-400 hover:text-cyan-300 transition-colors group">
                عرض <ArrowLeft class="w-3.5 h-3.5 group-hover:-translate-x-1 transition-transform" />
              </Link>
            </td>
          </tr>
          <tr v-if="!loading && orders.length === 0">
            <td colspan="8" class="px-5 py-16 text-center">
              <EmptyState :icon="ShoppingBag" title="لا توجد طلبات" description="لم تقم بأي عملية شراء بعد." />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
