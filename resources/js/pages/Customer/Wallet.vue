<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import EmptyState from '@/components/EmptyState.vue';
import {
  Wallet, ArrowLeft, TrendingUp, TrendingDown, RefreshCw,
  ShoppingBag, CreditCard, ArrowUpRight, Clock,
} from 'lucide-vue-next';

const api = useApi();
const auth = useAuthStore();
const toast = useToast();

const transactions = ref<any[]>([]);
const loading = ref(true);

const balance = computed(() => auth.user?.balance ?? 0);

const totalSpent = computed(() => transactions.value
  .filter(t => t.type === 'purchase' && t.status === 'completed')
  .reduce((sum, t) => sum + Number(t.amount), 0));

const totalDeposited = computed(() => transactions.value
  .filter(t => t.type === 'deposit' && t.status === 'completed')
  .reduce((sum, t) => sum + Number(t.amount), 0));

const typeMeta: Record<string, { icon: any; color: string; bg: string; label: string }> = {
  purchase: { icon: ShoppingBag, color: 'text-red-400', bg: 'bg-red-500/10', label: 'شراء' },
  deposit: { icon: CreditCard, color: 'text-emerald-400', bg: 'bg-emerald-500/10', label: 'إيداع' },
  refund: { icon: ArrowUpRight, color: 'text-amber-400', bg: 'bg-amber-500/10', label: 'استرجاع' },
};

const statusMeta: Record<string, { color: string; label: string }> = {
  pending: { color: 'text-amber-400', label: 'قيد المعالجة' },
  completed: { color: 'text-emerald-400', label: 'مكتمل' },
  rejected: { color: 'text-red-400', label: 'مرفوض' },
};

async function loadTransactions() {
  loading.value = true;
  try {
    const { data } = await api.get('/transactions');
    transactions.value = data.data ?? data ?? [];
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    loading.value = false;
  }
}

onMounted(loadTransactions);
</script>

<template>
  <Head title="محفظتي" />

  <div dir="rtl" class="min-h-screen bg-[#050507] text-white relative overflow-x-hidden" style="font-family: 'Cairo', 'Space Grotesk', sans-serif;">
    <!-- Ambient -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]" />
      <div class="absolute top-[-10%] left-1/2 -translate-x-1/2 w-[80%] h-[40%] bg-cyan-500/10 blur-[120px] rounded-full" />
    </div>

    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 pt-10 pb-20">
      <!-- Breadcrumb -->
      <div class="flex items-center gap-2 mb-8">
        <Link href="/dashboard" class="text-xs text-white/40 hover:text-cyan-400 transition-colors flex items-center gap-1">
          <ArrowLeft class="w-3 h-3" /> لوحة التحكم
        </Link>
        <span class="text-white/20">/</span>
        <span class="text-xs text-cyan-400">محفظتي</span>
      </div>

      <h1 class="text-3xl font-bold tracking-tight mb-2" style="font-family: 'Space Grotesk', sans-serif;">محفظتي</h1>
      <p class="text-sm text-white/40 mb-8">سجل الرصيد والحركات المالية</p>

      <!-- Balance Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden group">
          <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
          <div class="relative">
            <div class="flex items-center gap-2 mb-3">
              <Wallet class="w-4 h-4 text-cyan-400" />
              <span class="text-[10px] font-mono text-white/40 uppercase tracking-wider">الرصيد الحالي</span>
            </div>
            <p class="text-3xl font-bold font-mono text-cyan-400">${{ Number(balance).toFixed(2) }}</p>
          </div>
        </div>
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
          <div class="flex items-center gap-2 mb-3">
            <TrendingDown class="w-4 h-4 text-red-400" />
            <span class="text-[10px] font-mono text-white/40 uppercase tracking-wider">إجمالي الشراء</span>
          </div>
          <p class="text-3xl font-bold font-mono text-white">${{ totalSpent.toFixed(2) }}</p>
        </div>
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
          <div class="flex items-center gap-2 mb-3">
            <TrendingUp class="w-4 h-4 text-emerald-400" />
            <span class="text-[10px] font-mono text-white/40 uppercase tracking-wider">إجمالي الإيداع</span>
          </div>
          <p class="text-3xl font-bold font-mono text-white">${{ totalDeposited.toFixed(2) }}</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-3 mb-8">
        <Link href="/deposit" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold bg-cyan-400/10 text-cyan-400 border border-cyan-400/20 rounded-xl hover:bg-cyan-400/20 transition-all">
          <CreditCard class="w-3.5 h-3.5" /> إيداع رصيد
        </Link>
        <button @click="loadTransactions" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all">
          <RefreshCw class="w-3.5 h-3.5" /> تحديث
        </button>
      </div>

      <!-- Transactions List -->
      <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden backdrop-blur-xl">
        <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
          <h3 class="text-sm font-semibold flex items-center gap-2">
            <Clock class="w-4 h-4 text-cyan-400" /> سجل الحركات
          </h3>
          <span class="text-xs font-mono text-white/40">{{ transactions.length }} حركة</span>
        </div>

        <div v-if="loading" class="px-5 py-10 text-center text-white/30 font-mono text-sm">
          <div class="flex items-center justify-center gap-2">
            <div class="w-4 h-4 border-2 border-cyan-400/30 border-t-cyan-400 rounded-full animate-spin" />
            Loading...
          </div>
        </div>

        <EmptyState v-else-if="transactions.length === 0" :icon="Wallet" title="لا توجد حركات" description="لا توجد حركات بعد" />

        <div v-else class="divide-y divide-white/5">
          <div v-for="tx in transactions" :key="tx.id" class="px-5 py-4 flex items-center gap-4 hover:bg-white/[0.03] transition-colors">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" :class="typeMeta[tx.type]?.bg ?? 'bg-white/5'">
              <component :is="typeMeta[tx.type]?.icon ?? CreditCard" class="w-4 h-4" :class="typeMeta[tx.type]?.color ?? 'text-white/50'" />
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-white">{{ typeMeta[tx.type]?.label ?? tx.type }}</span>
                <span v-if="tx.order_id" class="text-[10px] font-mono text-white/30">#{{ tx.order_id }}</span>
              </div>
              <p class="text-xs text-white/40 font-mono mt-0.5">{{ new Date(tx.created_at ?? tx.date).toLocaleString('ar-SA') }}</p>
            </div>
            <div class="text-left">
              <p class="text-sm font-bold font-mono" :class="tx.type === 'purchase' ? 'text-red-400' : tx.type === 'deposit' ? 'text-emerald-400' : 'text-white'">
                {{ tx.type === 'purchase' ? '-' : '+' }}${{ Number(tx.amount).toFixed(2) }}
              </p>
              <p class="text-[10px] font-mono mt-0.5" :class="statusMeta[tx.status]?.color ?? 'text-white/30'">
                {{ statusMeta[tx.status]?.label ?? tx.status }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>