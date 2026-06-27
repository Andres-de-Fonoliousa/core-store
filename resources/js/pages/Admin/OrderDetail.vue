<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import {
  ArrowRight, Package, CheckCircle, Clock, XCircle, Zap,
  User, DollarSign, RefreshCw, ArrowLeft, Copy, Check,
  AlertTriangle, RotateCcw, ExternalLink,
} from 'lucide-vue-next';

const props = defineProps<{ id: number }>();
const api = useApi();
const toast = useToast();

const order = ref<any>(null);
const loading = ref(true);
const actionLoading = ref(false);
const copied = ref(false);

const statusMeta: Record<string, { color: string; bg: string; border: string; icon: any; label: string }> = {
  pending_payment: { color: 'text-amber-400', bg: 'bg-amber-500/10', border: 'border-amber-400/20', icon: Clock, label: 'في انتظار الدفع' },
  paid: { color: 'text-cyan-400', bg: 'bg-cyan-500/10', border: 'border-cyan-400/20', icon: Zap, label: 'مدفوع' },
  fulfilled: { color: 'text-emerald-400', bg: 'bg-emerald-500/10', border: 'border-emerald-400/20', icon: CheckCircle, label: 'مكتمل' },
  cancelled: { color: 'text-red-400', bg: 'bg-red-500/10', border: 'border-red-400/20', icon: XCircle, label: 'ملغي' },
};

async function loadOrder() {
  loading.value = true;
  try {
    const { data } = await api.get(`/admin/orders/${props.id}`);
    order.value = data.data ?? data;
  } catch (error) {
    toast.error('فشل تحميل تفاصيل الطلب');
  } finally {
    loading.value = false;
  }
}

async function retryFulfillment() {
  actionLoading.value = true;
  try {
    await api.post(`/admin/orders/${props.id}/retry`);
    toast.success('تمت إعادة محاولة التوصيل');
    await loadOrder();
  } catch (error) {
    toast.error('فشلت إعادة المحاولة');
  } finally {
    actionLoading.value = false;
  }
}

async function cancelOrder() {
  if (!confirm('هل أنت متأكد من إلغاء هذا الطلب؟')) return;
  actionLoading.value = true;
  try {
    await api.post(`/admin/orders/${props.id}/cancel`);
    toast.success('تم إلغاء الطلب');
    await loadOrder();
  } catch (error) {
    toast.error('فشل الإلغاء');
  } finally {
    actionLoading.value = false;
  }
}

function copyCode() {
  if (!order.value?.serial_code) return;
  navigator.clipboard.writeText(order.value.serial_code);
  copied.value = true;
  toast.success('تم نسخ الكود');
  setTimeout(() => copied.value = false, 2000);
}

onMounted(loadOrder);
</script>

<template>
  <Head title="Order Detail" />

  <div class="space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2">
      <Link href="/admin/orders" class="text-xs text-white/40 hover:text-cyan-400 transition-colors flex items-center gap-1">
        <ArrowRight class="w-3 h-3" /> Orders
      </Link>
      <span class="text-white/20">/</span>
      <span class="text-xs text-cyan-400 font-mono">#{{ id }}</span>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="h-8 bg-white/5 rounded-lg w-1/3 animate-pulse" />
      <div class="h-48 bg-white/[0.02] border border-white/5 rounded-2xl animate-pulse" />
    </div>

    <div v-else-if="!order" class="text-center py-20">
      <XCircle class="w-10 h-10 text-white/20 mx-auto mb-3" />
      <p class="text-white/40">Order not found</p>
    </div>

    <div v-else class="space-y-6">
      <!-- Header Card -->
      <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1" :class="statusMeta[order.status]?.bg.replace('/10', '/40') ?? 'bg-white/10'" />
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 mb-2">
              <span class="text-xs font-mono text-white/40 tracking-wider">ORDER</span>
              <span class="text-xs font-mono text-cyan-400">#{{ order.id }}</span>
            </div>
            <h1 class="text-2xl font-bold">{{ order.product?.name ?? 'Unknown Product' }}</h1>
            <p class="text-sm text-white/40 mt-1">{{ new Date(order.created_at).toLocaleString() }}</p>
          </div>
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border backdrop-blur-sm self-start" :class="[
            statusMeta[order.status]?.bg,
            statusMeta[order.status]?.border,
            statusMeta[order.status]?.color
          ]">
            <component :is="statusMeta[order.status]?.icon" class="w-4 h-4" />
            <span class="text-xs font-semibold">{{ statusMeta[order.status]?.label }}</span>
          </div>
        </div>

        <!-- Product + Customer Row -->
        <div class="mt-6 grid md:grid-cols-2 gap-4">
          <!-- Product -->
          <div class="flex items-center gap-4 bg-white/[0.02] border border-white/5 rounded-xl p-4">
            <div class="w-16 h-16 rounded-lg bg-white/5 border border-white/10 overflow-hidden flex-shrink-0">
              <img :src="order.product?.image || '/placeholder-product.png'" class="w-full h-full object-cover" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-white truncate">{{ order.product?.name }}</p>
              <p class="text-xs text-white/40 mt-1">{{ order.product?.category?.name ?? 'General' }}</p>
              <p class="text-xs text-cyan-400 font-mono mt-1">${{ Number(order.price_at_time_of_order).toFixed(2) }} × {{ order.quantity }}</p>
            </div>
          </div>
          <!-- Customer -->
          <div class="flex items-center gap-4 bg-white/[0.02] border border-white/5 rounded-xl p-4">
            <div class="w-12 h-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center flex-shrink-0">
              <User class="w-5 h-5 text-white/50" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-white">{{ order.user?.name ?? 'Unknown' }}</p>
              <p class="text-xs text-white/40 mt-1 font-mono">{{ order.user?.email }}</p>
              <p class="text-xs text-white/30 mt-1">ID: {{ order.user?.id }}</p>
            </div>
            <Link :href="`/admin/users/${order.user?.id}`" class="text-cyan-400 hover:text-cyan-300 transition-colors">
              <ExternalLink class="w-4 h-4" />
            </Link>
          </div>
        </div>
      </div>

      <!-- Serial Code (if fulfilled) -->
      <div v-if="order.status === 'fulfilled' && order.serial_code" class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent" />
        <div class="relative">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
              <CheckCircle class="w-4 h-4 text-emerald-400" />
              <span class="text-sm font-semibold">Serial Code</span>
            </div>
            <button @click="copyCode" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs bg-white/5 border border-white/10 rounded-lg hover:bg-emerald-400/10 hover:text-emerald-400 hover:border-emerald-400/30 transition-all">
              <component :is="copied ? Check : Copy" class="w-3.5 h-3.5" />
              {{ copied ? 'Copied' : 'Copy' }}
            </button>
          </div>
          <div class="bg-black/30 border border-emerald-400/20 rounded-xl px-4 py-4 font-mono text-lg tracking-wider text-emerald-300 select-all">
            {{ order.serial_code }}
          </div>
        </div>
      </div>

      <!-- Failed State -->
      <div v-if="order.fulfillment_status === 'failed'" class="bg-red-500/5 border border-red-400/20 rounded-2xl p-6">
        <div class="flex items-start gap-3">
          <AlertTriangle class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" />
          <div class="flex-1">
            <h3 class="text-sm font-semibold text-red-400">Fulfillment Failed</h3>
            <p class="text-sm text-white/50 mt-1">{{ order.fail_reason ?? 'Provider API returned an error. Manual intervention required.' }}</p>
            <div class="flex items-center gap-3 mt-4">
              <button @click="retryFulfillment" :disabled="actionLoading" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold bg-red-400/10 text-red-400 border border-red-400/20 rounded-lg hover:bg-red-400/20 transition-all disabled:opacity-50">
                <RotateCcw class="w-3.5 h-3.5" /> Retry
              </button>
              <button @click="cancelOrder" :disabled="actionLoading" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold bg-white/5 text-white/60 border border-white/10 rounded-lg hover:bg-white/10 transition-all disabled:opacity-50">
                <XCircle class="w-3.5 h-3.5" /> Cancel & Refund
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Details Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4">
          <p class="text-[10px] font-mono text-white/40 uppercase tracking-wider mb-1">Unit Price</p>
          <p class="text-lg font-bold font-mono text-white">${{ Number(order.price_at_time_of_order).toFixed(2) }}</p>
        </div>
        <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4">
          <p class="text-[10px] font-mono text-white/40 uppercase tracking-wider mb-1">Quantity</p>
          <p class="text-lg font-bold font-mono text-white">×{{ order.quantity }}</p>
        </div>
        <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4">
          <p class="text-[10px] font-mono text-white/40 uppercase tracking-wider mb-1">Total</p>
          <p class="text-lg font-bold font-mono text-cyan-400">${{ Number(order.price_at_time_of_order * order.quantity).toFixed(2) }}</p>
        </div>
        <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4">
          <p class="text-[10px] font-mono text-white/40 uppercase tracking-wider mb-1">Transaction</p>
          <Link v-if="order.transaction_id" :href="`/admin/transactions/${order.transaction_id}`" class="text-lg font-bold font-mono text-cyan-400 hover:underline">
            #{{ order.transaction_id }}
          </Link>
          <p v-else class="text-lg font-bold font-mono text-white/30">—</p>
        </div>
      </div>

      <!-- Details JSON -->
      <div v-if="order.details" class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
        <h3 class="text-sm font-semibold mb-3 flex items-center gap-2">
          <Package class="w-4 h-4 text-cyan-400" /> Order Details
        </h3>
        <pre class="text-xs font-mono text-white/50 bg-black/20 rounded-xl p-4 overflow-x-auto">{{ JSON.stringify(order.details, null, 2) }}</pre>
      </div>

      <!-- Timeline -->
      <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
        <h3 class="text-sm font-semibold mb-5 flex items-center gap-2">
          <Clock class="w-4 h-4 text-cyan-400" /> Timeline
        </h3>
        <div class="relative space-y-6">
          <div class="absolute left-[7px] top-2 bottom-2 w-px bg-white/10" />
          <div class="relative flex items-start gap-4">
            <div class="w-3.5 h-3.5 rounded-full bg-cyan-400 border-4 border-[#0a0a0c] z-10 mt-1" />
            <div>
              <p class="text-sm font-medium text-white">Order Placed</p>
              <p class="text-xs text-white/40 font-mono mt-0.5">{{ new Date(order.created_at).toLocaleString() }}</p>
            </div>
          </div>
          <div v-if="order.status !== 'pending_payment'" class="relative flex items-start gap-4">
            <div class="w-3.5 h-3.5 rounded-full bg-cyan-400 border-4 border-[#0a0a0c] z-10 mt-1" />
            <div>
              <p class="text-sm font-medium text-white">Payment Confirmed</p>
              <p class="text-xs text-white/40 font-mono mt-0.5">Balance deducted from user account</p>
            </div>
          </div>
          <div v-if="order.status === 'fulfilled'" class="relative flex items-start gap-4">
            <div class="w-3.5 h-3.5 rounded-full bg-emerald-400 border-4 border-[#0a0a0c] z-10 mt-1" />
            <div>
              <p class="text-sm font-medium text-white">Fulfilled</p>
              <p class="text-xs text-white/40 font-mono mt-0.5">{{ order.updated_at ? new Date(order.updated_at).toLocaleString() : '—' }}</p>
            </div>
          </div>
          <div v-if="order.fulfillment_status === 'failed'" class="relative flex items-start gap-4">
            <div class="w-3.5 h-3.5 rounded-full bg-red-400 border-4 border-[#0a0a0c] z-10 mt-1" />
            <div>
              <p class="text-sm font-medium text-white">Fulfillment Failed</p>
              <p class="text-xs text-white/40 font-mono mt-0.5">{{ order.fail_reason ?? 'Provider error' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-3">
        <Link href="/admin/orders" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all">
          <ArrowLeft class="w-3.5 h-3.5" /> Back to Orders
        </Link>
        <button @click="loadOrder" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all">
          <RefreshCw class="w-3.5 h-3.5" /> Refresh
        </button>
      </div>
    </div>
  </div>
</template>