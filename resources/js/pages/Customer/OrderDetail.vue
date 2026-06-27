<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import SerialDisplay from '@/components/SerialDisplay.vue';
import {
  ArrowRight, XCircle, CheckCircle, Clock, RefreshCw,
  ArrowLeft, ShoppingBag,
} from 'lucide-vue-next';

const props = defineProps<{ id: number }>();
const api = useApi();
const toast = useToast();

const order = ref<any>(null);
const loading = ref(true);

const statusMeta: Record<string, { color: string; bg: string; border: string; label: string }> = {
  pending:   { color: 'text-amber-400',  bg: 'bg-amber-500/10',  border: 'border-amber-400/20',  label: 'قيد التنفيذ' },
  paid:      { color: 'text-blue-400',   bg: 'bg-blue-500/10',   border: 'border-blue-400/20',   label: 'تم الدفع' },
  fulfilled: { color: 'text-emerald-400', bg: 'bg-emerald-500/10', border: 'border-emerald-400/20', label: 'تم التوصيل' },
  failed:    { color: 'text-red-400',    bg: 'bg-red-500/10',    border: 'border-red-400/20',    label: 'فشل' },
  refunded:  { color: 'text-white/60',  bg: 'bg-white/5',       border: 'border-white/10',      label: 'مسترجع' },
};

function statusIcon(status: string) {
  if (status === 'pending' || status === 'paid') return Clock;
  if (status === 'fulfilled') return CheckCircle;
  return XCircle;
}

async function loadOrder() {
  loading.value = true;
  try {
    const res = await api.get(`/orders/${props.id}`);
    order.value = res.data.data ?? res.data;
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    loading.value = false;
  }
}

loadOrder();
</script>

<template>
  <Head title="تفاصيل الطلب" />

  <div class="max-w-3xl mx-auto pt-4">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 mb-8">
      <Link href="/orders" class="text-xs text-white/40 hover:text-cyan-400 transition-colors flex items-center gap-1">
        <ArrowRight class="w-3 h-3" /> طلباتي
      </Link>
      <span class="text-white/20">/</span>
      <span class="text-xs text-cyan-400 font-mono">#{{ id }}</span>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="h-8 bg-white/5 rounded-lg w-1/3 animate-pulse" />
      <div class="h-48 bg-white/[0.02] border border-white/5 rounded-2xl animate-pulse" />
      <div class="h-32 bg-white/[0.02] border border-white/5 rounded-2xl animate-pulse" />
    </div>

    <div v-else-if="!order" class="text-center py-20">
      <XCircle class="w-10 h-10 text-white/20 mx-auto mb-3" />
      <p class="text-white/40">لم يتم العثور على الطلب</p>
    </div>

    <div v-else class="space-y-6">
      <!-- Success Banner (only if fulfilled) -->
      <div v-if="order.fulfillment_status === 'fulfilled'" class="bg-emerald-500/5 border border-emerald-400/20 rounded-2xl p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center flex-shrink-0">
          <CheckCircle class="w-6 h-6 text-emerald-400" />
        </div>
        <div>
          <h2 class="text-base font-bold text-white">تم توصيل طلبك بنجاح!</h2>
          <p class="text-sm text-white/50 mt-0.5">المنتج جاهز للاستخدام. تجد الكود أدناه.</p>
        </div>
      </div>

      <!-- Failure Banner -->
      <div v-if="order.fulfillment_status === 'failed'" class="bg-red-500/5 border border-red-400/20 rounded-2xl p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-red-500/10 border border-red-400/20 flex items-center justify-center flex-shrink-0">
          <XCircle class="w-6 h-6 text-red-400" />
        </div>
        <div>
          <h2 class="text-base font-bold text-white">فشل التوصيل التلقائي</h2>
          <p class="text-sm text-white/50 mt-0.5">لم يتمكن النظام من توصيل المنتج تلقائياً. يرجى التواصل مع الدعم الفني.</p>
          <p v-if="order.fail_reason" class="text-xs text-red-400/80 mt-2 bg-red-500/5 rounded-lg p-2 border border-red-400/10 font-mono">{{ order.fail_reason }}</p>
        </div>
      </div>

      <!-- Order Header -->
      <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1" :class="statusMeta[order.status]?.bg?.replace('/10', '/40') || 'bg-white/10'" />
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 mb-2">
              <span class="text-xs font-mono text-white/40 tracking-wider">رقم الطلب</span>
              <span class="text-xs font-mono text-cyan-400">#{{ order.id }}</span>
            </div>
            <h1 class="text-xl font-bold">{{ order.product?.name || 'منتج غير معروف' }}</h1>
            <p class="text-sm text-white/40 mt-1">{{ new Date(order.created_at).toLocaleString('ar-SA', { dateStyle: 'full', timeStyle: 'short' }) }}</p>
          </div>
          <div v-if="statusMeta[order.status]" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border backdrop-blur-sm self-start" :class="[statusMeta[order.status]?.bg, statusMeta[order.status]?.border, statusMeta[order.status]?.color]">
            <component :is="statusIcon(order.status)" class="w-4 h-4" />
            <span class="text-xs font-semibold">{{ statusMeta[order.status]?.label }}</span>
          </div>
        </div>

        <!-- Product Card -->
        <div class="mt-6 flex items-center gap-4 bg-white/[0.02] border border-white/5 rounded-xl p-4">
          <div class="w-16 h-16 rounded-lg bg-white/5 border border-white/10 overflow-hidden flex-shrink-0">
            <img :src="order.product?.image || '/placeholder-product.png'" class="w-full h-full object-cover" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-white truncate">{{ order.product?.name }}</p>
            <p class="text-xs text-white/40 mt-1">{{ order.product?.category?.name || 'عام' }}</p>
          </div>
          <div class="text-left">
            <p class="text-lg font-bold font-mono text-cyan-400">${{ Number(order.price_at_time_of_order || order.product?.price || 0).toFixed(2) }}</p>
          </div>
        </div>
      </div>

      <!-- Serial Code -->
      <div v-if="order.fulfillment_status === 'fulfilled' && order.serial_code" class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
        <div class="relative">
          <SerialDisplay :code="order.serial_code" :fulfilled="true" />
        </div>
      </div>

      <!-- Order Details Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4">
          <p class="text-[10px] font-mono text-white/40 uppercase tracking-wider mb-1">السعر</p>
          <p class="text-lg font-bold font-mono text-white">${{ Number(order.price_at_time_of_order || 0).toFixed(2) }}</p>
        </div>
        <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4">
          <p class="text-[10px] font-mono text-white/40 uppercase tracking-wider mb-1">الكمية</p>
          <p class="text-lg font-bold font-mono text-white">×{{ order.quantity || 1 }}</p>
        </div>
        <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4">
          <p class="text-[10px] font-mono text-white/40 uppercase tracking-wider mb-1">الإجمالي</p>
          <p class="text-lg font-bold font-mono text-cyan-400">${{ Number(order.total_price || order.price_at_time_of_order || 0).toFixed(2) }}</p>
        </div>
        <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4">
          <p class="text-[10px] font-mono text-white/40 uppercase tracking-wider mb-1">طريقة الدفع</p>
          <p class="text-lg font-bold font-mono text-white">{{ order.payment_method || 'رصيد' }}</p>
        </div>
      </div>

      <!-- Timeline -->
      <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
        <h3 class="text-sm font-semibold mb-5 flex items-center gap-2">
          <Clock class="w-4 h-4 text-cyan-400" /> سير الطلب
        </h3>
        <div class="relative space-y-6">
          <div class="absolute right-[7px] top-2 bottom-2 w-px bg-white/10" />

          <!-- Placed -->
          <div class="relative flex items-start gap-4">
            <div class="w-3.5 h-3.5 rounded-full bg-cyan-400 border-4 border-[#050507] z-10 mt-1" />
            <div>
              <p class="text-sm font-medium text-white">تم تقديم الطلب</p>
              <p class="text-xs text-white/40 font-mono mt-0.5">{{ new Date(order.created_at).toLocaleString('ar-SA') }}</p>
            </div>
          </div>

          <!-- Processing (always shown) -->
          <div class="relative flex items-start gap-4">
            <div class="w-3.5 h-3.5 rounded-full border-4 border-[#050507] z-10 mt-1" :class="['fulfilled', 'failed'].includes(order.fulfillment_status) ? 'bg-cyan-400' : 'bg-white/20 animate-pulse'" />
            <div>
              <p class="text-sm font-medium" :class="['fulfilled', 'failed'].includes(order.fulfillment_status) ? 'text-white' : 'text-white/60'">معالجة الطلب</p>
              <p class="text-xs text-white/40 font-mono mt-0.5">التحقق من الدفع والمخزون</p>
            </div>
          </div>

          <!-- Delivered / Failed (only when fulfillment has a terminal status) -->
          <div v-if="['fulfilled', 'failed', 'cancelled'].includes(order.fulfillment_status)" class="relative flex items-start gap-4">
            <div class="w-3.5 h-3.5 rounded-full border-4 border-[#050507] z-10 mt-1" :class="order.fulfillment_status === 'fulfilled' ? 'bg-emerald-400' : 'bg-red-400'" />
            <div>
              <p class="text-sm font-medium text-white">
                {{ order.fulfillment_status === 'fulfilled' ? 'تم التوصيل' : 'فشل الطلب' }}
              </p>
              <p class="text-xs text-white/40 font-mono mt-0.5">
                {{ order.updated_at ? new Date(order.updated_at).toLocaleString('ar-SA') : '—' }}
              </p>
              <p v-if="order.fulfillment_status === 'failed' && order.fail_reason" class="text-xs text-red-400/80 mt-1 bg-red-500/5 rounded-lg p-2 border border-red-400/10">
                {{ order.fail_reason }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-3">
        <Link href="/orders" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all">
          <ArrowLeft class="w-3.5 h-3.5" /> طلباتي
        </Link>
        <Link href="/products" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold bg-cyan-400/10 text-cyan-400 border border-cyan-400/20 rounded-xl hover:bg-cyan-400/20 transition-all">
          <ShoppingBag class="w-3.5 h-3.5" /> تصفح المنتجات
        </Link>
        <button @click="loadOrder" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all mr-auto">
          <RefreshCw class="w-3.5 h-3.5" /> تحديث
        </button>
      </div>
    </div>
  </div>
</template>
