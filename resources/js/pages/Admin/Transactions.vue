<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import { useUiStore } from '@/stores/uiStore';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import {
  ArrowLeftRight, CheckCircle, User, DollarSign, RefreshCw,
} from 'lucide-vue-next';

const api = useApi();
const toast = useToast();
const ui = useUiStore();
const transactions = ref<any[]>([]);
const loading = ref(false);
const actionLoading = ref(false);
const showConfirm = ref(false);
const selectedTransaction = ref<any>(null);

ui.setPageTitle('Transactions');

async function loadTransactions() {
  loading.value = true;
  try {
    const { data } = await api.get('/admin/transactions');
    transactions.value = data.data ?? data ?? [];
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    loading.value = false;
  }
}

function openApprove(transaction: any) {
  selectedTransaction.value = transaction;
  showConfirm.value = true;
}

async function handleApprove() {
  if (!selectedTransaction.value) return;
  actionLoading.value = true;
  try {
    await api.post(`/admin/transactions/${selectedTransaction.value.id}/approve`);
    toast.success('Transaction approved.');
    showConfirm.value = false;
    await loadTransactions();
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    actionLoading.value = false;
  }
}

onMounted(loadTransactions);
</script>

<template>
  <Head title="Transactions" />
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">Finance</span>
        <h1 class="text-3xl font-bold tracking-tight mt-1">Transactions</h1>
        <p class="text-sm text-white/40 mt-1">Review and approve pending transactions.</p>
      </div>
      <button @click="loadTransactions" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all active:scale-95">
        <RefreshCw class="w-3.5 h-3.5" /> Refresh
      </button>
    </div>

    <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden backdrop-blur-xl">
      <table class="min-w-full text-sm">
        <thead class="bg-white/5 text-left text-xs font-mono uppercase tracking-[0.12em] text-white/40">
          <tr><th class="px-5 py-4">ID</th><th class="px-5 py-4">User</th><th class="px-5 py-4">Amount</th><th class="px-5 py-4">Type</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Date</th><th class="px-5 py-4 text-right">Actions</th></tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="7" class="px-5 py-10 text-center text-white/30 font-mono text-sm"><div class="flex items-center justify-center gap-2"><div class="w-4 h-4 border-2 border-cyan-400/30 border-t-cyan-400 rounded-full animate-spin" /> Loading...</div></td></tr>
          <tr v-for="tx in transactions" :key="tx.id" class="border-t border-white/5 hover:bg-white/[0.03] transition-colors">
            <td class="px-5 py-4 font-mono text-white/70">#{{ tx.id }}</td>
            <td class="px-5 py-4"><div class="flex items-center gap-2"><div class="w-7 h-7 rounded-full bg-white/5 border border-white/10 flex items-center justify-center"><User class="w-3.5 h-3.5 text-white/50" /></div><span class="text-white/80">{{ tx.user?.name ?? tx.user?.email ?? 'Unknown' }}</span></div></td>
            <td class="px-5 py-4 font-mono font-semibold" :class="tx.amount >= 0 ? 'text-emerald-400' : 'text-red-400'">{{ tx.amount >= 0 ? '+' : '' }}${{ Number(tx.amount).toFixed(2) }}</td>
            <td class="px-5 py-4 text-white/50 text-xs font-mono uppercase">{{ tx.type }}</td>
            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-[11px] font-mono font-semibold border" :class="tx.status === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-400/20' : tx.status === 'pending' ? 'bg-amber-500/10 text-amber-400 border-amber-400/20' : 'bg-white/5 text-white/40 border-white/10'">{{ tx.status }}</span></td>
            <td class="px-5 py-4 text-white/40 font-mono text-xs">{{ new Date(tx.created_at).toLocaleDateString() }}</td>
            <td class="px-5 py-4 text-right">
              <button v-if="tx.status === 'pending'" @click="openApprove(tx)" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold bg-emerald-400/10 text-emerald-400 border border-emerald-400/20 rounded-lg hover:bg-emerald-400/20 transition-all active:scale-95"><CheckCircle class="w-3.5 h-3.5" /> Approve</button>
              <span v-else class="text-xs text-white/20 font-mono">—</span>
            </td>
          </tr>
          <tr v-if="!loading && transactions.length === 0"><td colspan="7" class="px-5 py-16 text-center"><div class="flex flex-col items-center justify-center text-white/30"><ArrowLeftRight class="w-8 h-8 mb-3 text-white/20" /><p class="text-sm font-medium">No transactions found.</p></div></td></tr>
        </tbody>
      </table>
    </div>

    <ConfirmDialog :open="showConfirm" title="Approve Transaction" :message="`Approve the $${Number(selectedTransaction?.amount ?? 0).toFixed(2)} transaction from ${selectedTransaction?.user?.name ?? 'this user'}?`" confirm-text="Approve" :loading="actionLoading" @update:open="showConfirm = $event" @confirm="handleApprove" @cancel="showConfirm = false" />
  </div>
</template>
