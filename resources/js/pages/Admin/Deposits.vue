<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import { useUiStore } from '@/stores/uiStore';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import OrderStatusBadge from '@/components/OrderStatusBadge.vue';
import {
  CreditCard, CheckCircle, XCircle, User, RefreshCw,
  Image, X, ExternalLink, Smartphone, Banknote,
} from 'lucide-vue-next';

const api = useApi();
const toast = useToast();
const ui = useUiStore();

const deposits = ref<any[]>([]);
const loading = ref(false);
const actionLoading = ref(false);
const showConfirm = ref(false);
const selectedDeposit = ref<any>(null);
const confirmAction = ref<'approve' | 'reject'>('approve');

const showPhotoModal = ref(false);
const photoUrl = ref<string | null>(null);

ui.setPageTitle('Deposits');

async function loadDeposits() {
  loading.value = true;
  try {
    const { data } = await api.get('/admin/deposits');
    deposits.value = data.data ?? data ?? [];
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    loading.value = false;
  }
}

function openPhoto(url: string | null) {
  if (!url) return;
  photoUrl.value = url;
  showPhotoModal.value = true;
}

function openConfirm(deposit: any, action: 'approve' | 'reject') {
  selectedDeposit.value = deposit;
  confirmAction.value = action;
  showConfirm.value = true;
}

async function handleConfirm() {
  if (!selectedDeposit.value) return;
  actionLoading.value = true;
  try {
    if (confirmAction.value === 'approve') {
      await api.post(`/admin/deposits/${selectedDeposit.value.id}/approve`);
      toast.success('Deposit approved.');
    } else {
      await api.post(`/admin/deposits/${selectedDeposit.value.id}/reject`);
      toast.success('Deposit rejected.');
    }
    showConfirm.value = false;
    await loadDeposits();
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    actionLoading.value = false;
  }
}

onMounted(loadDeposits);
</script>

<template>
  <Head title="Deposits" />
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">Finance</span>
        <h1 class="text-3xl font-bold tracking-tight mt-1">Deposit Requests</h1>
        <p class="text-sm text-white/40 mt-1">Review proof images and approve pending deposits.</p>
      </div>
      <button @click="loadDeposits" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all active:scale-95">
        <RefreshCw class="w-3.5 h-3.5" /> Refresh
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden backdrop-blur-xl">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-white/5 text-left text-xs font-mono uppercase tracking-[0.12em] text-white/40">
            <tr>
              <th class="px-5 py-4">ID</th>
              <th class="px-5 py-4">User</th>
              <th class="px-5 py-4">Amount</th>
              <th class="px-5 py-4">Method</th>
              <th class="px-5 py-4">Proof</th>
              <th class="px-5 py-4">Note</th>
              <th class="px-5 py-4">Status</th>
              <th class="px-5 py-4">Date</th>
              <th class="px-5 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="9" class="px-5 py-10 text-center text-white/30 font-mono text-sm">
                <div class="flex items-center justify-center gap-2">
                  <div class="w-4 h-4 border-2 border-cyan-400/30 border-t-cyan-400 rounded-full animate-spin" />
                  Loading...
                </div>
              </td>
            </tr>

            <tr v-for="deposit in deposits" :key="deposit.id" class="border-t border-white/5 hover:bg-white/[0.03] transition-colors">
              <td class="px-5 py-4 font-mono text-white/70">#{{ deposit.id }}</td>
              <td class="px-5 py-4">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-white/5 border border-white/10 flex items-center justify-center">
                    <User class="w-3.5 h-3.5 text-white/50" />
                  </div>
                  <span class="text-white/80">{{ deposit.user?.name ?? deposit.user?.email ?? 'Unknown' }}</span>
                </div>
              </td>
              <td class="px-5 py-4 font-mono text-cyan-400 font-semibold">${{ Number(deposit.amount).toFixed(2) }}</td>
              <td class="px-5 py-4">
                <span v-if="deposit.payment_method" class="inline-flex items-center gap-1.5 px-2 py-1 text-[11px] font-medium rounded-lg border font-mono"
                  :class="deposit.payment_method?.code === 'sham_cash' ? 'bg-purple-500/10 text-purple-400 border-purple-400/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-400/20'">
                  <Smartphone v-if="deposit.payment_method?.code === 'sham_cash'" class="w-3 h-3" />
                  <Banknote v-else class="w-3 h-3" />
                  {{ deposit.payment_method?.name?.includes('Sham') ? 'Sham Cash' : 'يدوي' }}
                </span>
                <span v-else class="text-xs text-white/20 font-mono">—</span>
              </td>
              <td class="px-5 py-4">
                <button v-if="deposit.proof_url" @click="openPhoto(deposit.proof_url)" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-[11px] font-medium bg-white/5 text-cyan-400 border border-cyan-400/20 rounded-lg hover:bg-cyan-400/10 transition-all">
                  <Image class="w-3.5 h-3.5" /> View
                </button>
                <span v-else-if="deposit.payment_method?.code === 'sham_cash'" class="text-xs text-purple-400/50 font-mono">آلي</span>
                <span v-else class="text-xs text-white/20 font-mono">—</span>
              </td>
              <td class="px-5 py-4 max-w-[200px] truncate text-white/40 text-xs">{{ deposit.note || (deposit.payment_id ? `TX: ${deposit.payment_id}` : '—') }}</td>
              <td class="px-5 py-4">
                <OrderStatusBadge :status="deposit.status" type="deposit" />
              </td>
              <td class="px-5 py-4 text-white/40 font-mono text-xs">{{ new Date(deposit.created_at).toLocaleDateString() }}</td>
              <td class="px-5 py-4 text-right">
                <div v-if="deposit.status === 'pending'" class="flex justify-end gap-2">
                  <button @click="openConfirm(deposit, 'approve')" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold bg-emerald-400/10 text-emerald-400 border border-emerald-400/20 rounded-lg hover:bg-emerald-400/20 transition-all active:scale-95">
                    <CheckCircle class="w-3.5 h-3.5" /> Approve
                  </button>
                  <button @click="openConfirm(deposit, 'reject')" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold bg-red-400/10 text-red-400 border border-red-400/20 rounded-lg hover:bg-red-400/20 transition-all active:scale-95">
                    <XCircle class="w-3.5 h-3.5" /> Reject
                  </button>
                </div>
                <span v-else class="text-xs text-white/20 font-mono">—</span>
              </td>
            </tr>

            <tr v-if="!loading && deposits.length === 0">
              <td colspan="9" class="px-5 py-16 text-center">
                <div class="flex flex-col items-center justify-center text-white/30">
                  <CreditCard class="w-8 h-8 mb-3 text-white/20" />
                  <p class="text-sm font-medium">No deposit requests found.</p>
                  <p class="text-xs font-mono mt-1">All caught up.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Photo Modal -->
    <Teleport to="body">
      <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="showPhotoModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" @click.self="showPhotoModal = false">
          <div class="relative max-w-3xl w-full">
            <button @click="showPhotoModal = false" class="absolute -top-10 right-0 w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-white/60 hover:text-white hover:bg-white/10 transition-all">
              <X class="w-4 h-4" />
            </button>
            <div class="bg-white/[0.03] border border-white/10 rounded-2xl overflow-hidden backdrop-blur-xl">
              <img :src="photoUrl!" alt="Deposit proof" class="w-full max-h-[70vh] object-contain" />
              <div class="px-5 py-3 flex items-center justify-between border-t border-white/5">
                <span class="text-xs font-mono text-white/40">Deposit Proof</span>
                <a :href="photoUrl!" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-cyan-400 hover:text-cyan-300 transition-colors">
                  <ExternalLink class="w-3 h-3" /> Open original
                </a>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Confirm Dialog -->
    <ConfirmDialog
      :open="showConfirm"
      :title="confirmAction === 'approve' ? 'Approve Deposit' : 'Reject Deposit'"
      :message="`Are you sure you want to ${confirmAction} the $${Number(selectedDeposit?.amount ?? 0).toFixed(2)} deposit from ${selectedDeposit?.user?.name ?? 'this user'}?`"
      :confirm-text="confirmAction === 'approve' ? 'Approve' : 'Reject'"
      :confirm-variant="confirmAction === 'approve' ? 'default' : 'destructive'"
      :loading="actionLoading"
      @update:open="showConfirm = $event"
      @confirm="handleConfirm"
      @cancel="showConfirm = false"
    />
  </div>
</template>
