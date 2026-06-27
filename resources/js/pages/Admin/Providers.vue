<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import { useUiStore } from '@/stores/uiStore';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import {
  Server, Plus, Pencil, Trash2, Save, X, CreditCard,
  Zap, RefreshCw,
} from 'lucide-vue-next';

const api = useApi();
const toast = useToast();
const ui = useUiStore();

const providers = ref<any[]>([]);
const loading = ref(false);
const saving = ref(false);
const showForm = ref(false);
const editingId = ref<number | null>(null);
const formName = ref('');
const formBaseUrl = ref('');
const formToken = ref('');
const formStatus = ref('active');
const formSyncActive = ref(true);
const showTopUp = ref(false);
const topUpProviderId = ref<number | null>(null);
const topUpAmount = ref(0);
const topUpLoading = ref(false);
const showDeleteConfirm = ref(false);
const deletingId = ref<number | null>(null);
const deletingName = ref('');

ui.setPageTitle('Providers');

async function loadProviders() {
  loading.value = true;
  try {
    const { data } = await api.get('/admin/providers');
    providers.value = data.data ?? data ?? [];
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    loading.value = false;
  }
}

function openCreate() {
  editingId.value = null;
  formName.value = ''; formBaseUrl.value = ''; formToken.value = ''; formStatus.value = 'active'; formSyncActive.value = true;
  showForm.value = true;
}

function openEdit(provider: any) {
  editingId.value = provider.id;
  formName.value = provider.name; formBaseUrl.value = provider.base_url; formToken.value = provider.token; formStatus.value = provider.status; formSyncActive.value = provider.sync_active;
  showForm.value = true;
}

async function saveProvider() {
  if (!formName.value.trim() || !formBaseUrl.value.trim() || !formToken.value.trim()) {
    toast.error('Please fill in required fields.'); return;
  }
  saving.value = true;
  try {
    const payload = { name: formName.value, base_url: formBaseUrl.value, token: formToken.value, sync_active: formSyncActive.value, status: formStatus.value };
    if (editingId.value) {
      await api.put(`/admin/providers/${editingId.value}`, payload);
      toast.success('Provider updated.');
    } else {
      await api.post('/admin/providers', payload);
      toast.success('Provider created.');
    }
    showForm.value = false;
    await loadProviders();
  } catch (error: any) {
    toast.error(parseApiError(error));
  } finally {
    saving.value = false;
  }
}

function confirmDelete(provider: any) { deletingId.value = provider.id; deletingName.value = provider.name; showDeleteConfirm.value = true; }
async function handleDelete() {
  if (!deletingId.value) return;
  try {
    await api.delete(`/admin/providers/${deletingId.value}`);
    toast.success('Provider deleted.'); showDeleteConfirm.value = false; await loadProviders();
  } catch (error) { toast.error(parseApiError(error)); }
}

function openTopUp(provider: any) { topUpProviderId.value = provider.id; topUpAmount.value = 0; showTopUp.value = true; }
async function handleTopUp() {
  if (!topUpProviderId.value || topUpAmount.value <= 0) { toast.error('Enter a valid amount.'); return; }
  topUpLoading.value = true;
  try {
    const { data } = await api.post(`/admin/providers/${topUpProviderId.value}/top-up`, { amount: topUpAmount.value });
    toast.success(`Topped up. New balance: $${Number(data.balance).toFixed(2)}`);
    showTopUp.value = false; await loadProviders();
  } catch (error) { toast.error(parseApiError(error)); }
  finally { topUpLoading.value = false; }
}

onMounted(loadProviders);
</script>

<template>
  <Head title="Providers" />
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">Integrations</span>
        <h1 class="text-3xl font-bold tracking-tight mt-1">Providers</h1>
        <p class="text-sm text-white/40 mt-1">API endpoints and sync management.</p>
      </div>
      <div class="flex gap-3">
        <button @click="loadProviders" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all active:scale-95">
          <RefreshCw class="w-3.5 h-3.5" /> Refresh
        </button>
        <button @click="openCreate" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 hover:shadow-[0_0_25px_-5px_rgba(34,211,238,0.5)] transition-all active:scale-95">
          <Plus class="w-4 h-4" /> Create
        </button>
      </div>
    </div>

    <div v-if="showForm" class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl space-y-5">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-white">{{ editingId ? 'Edit Provider' : 'New Provider' }}</h2>
        <button @click="showForm = false" class="text-white/40 hover:text-white transition-colors"><X class="w-5 h-5" /></button>
      </div>
      <div class="grid gap-4 sm:grid-cols-3">
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">Name</label><input v-model="formName" type="text" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-cyan-400/50 transition-all" placeholder="Provider name" /></div>
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">API URL</label><input v-model="formBaseUrl" type="text" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-cyan-400/50 transition-all" placeholder="https://provider.example/api" /></div>
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">API Token</label><input v-model="formToken" type="text" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-cyan-400/50 transition-all" placeholder="Secret token" /></div>
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">Status</label><select v-model="formStatus" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-400/50 transition-all appearance-none"><option value="active" class="bg-[#0a0a0c]">Active</option><option value="inactive" class="bg-[#0a0a0c]">Inactive</option></select></div>
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">Sync Active</label><select v-model="formSyncActive" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-400/50 transition-all appearance-none"><option :value="true" class="bg-[#0a0a0c]">Enabled</option><option :value="false" class="bg-[#0a0a0c]">Disabled</option></select></div>
      </div>
      <div class="flex gap-3">
        <button @click="saveProvider" :disabled="saving" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 transition-all active:scale-95 disabled:opacity-60"><Save class="w-4 h-4" /> {{ saving ? 'Saving...' : 'Save' }}</button>
        <button @click="showForm = false" class="px-5 py-2.5 text-sm font-medium text-white/70 border border-white/10 rounded-xl hover:bg-white/5 hover:text-white transition-all">Cancel</button>
      </div>
    </div>

    <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden backdrop-blur-xl">
      <table class="min-w-full text-sm">
        <thead class="bg-white/5 text-left text-xs font-mono uppercase tracking-[0.12em] text-white/40">
          <tr><th class="px-5 py-4">Name</th><th class="px-5 py-4">Balance</th><th class="px-5 py-4">Sync</th><th class="px-5 py-4">Status</th><th class="px-5 py-4 text-right">Actions</th></tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="px-5 py-10 text-center text-white/30 font-mono text-sm"><div class="flex items-center justify-center gap-2"><div class="w-4 h-4 border-2 border-cyan-400/30 border-t-cyan-400 rounded-full animate-spin" /> Loading...</div></td></tr>
          <tr v-for="provider in providers" :key="provider.id" class="border-t border-white/5 hover:bg-white/[0.03] transition-colors">
            <td class="px-5 py-4"><div class="flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center"><Server class="w-4 h-4 text-cyan-400" /></div><span class="font-medium text-white">{{ provider.name }}</span></div></td>
            <td class="px-5 py-4 font-mono text-cyan-400 font-semibold">${{ Number(provider.balance ?? 0).toFixed(2) }}</td>
            <td class="px-5 py-4"><span class="inline-flex items-center gap-1 text-xs font-mono" :class="provider.sync_active ? 'text-emerald-400' : 'text-white/30'"><Zap class="w-3 h-3" :class="provider.sync_active ? 'fill-emerald-400/20' : ''" /> {{ provider.sync_active ? 'Enabled' : 'Disabled' }}</span></td>
            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-[11px] font-mono font-semibold border" :class="provider.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-400/20' : 'bg-white/5 text-white/40 border-white/10'">{{ provider.status }}</span></td>
            <td class="px-5 py-4 text-right"><div class="flex justify-end gap-2"><button @click="openTopUp(provider)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-white/10 text-white/70 hover:text-amber-400 hover:border-amber-400/30 hover:bg-amber-500/5 transition-all"><CreditCard class="w-3.5 h-3.5" /> Top Up</button><button @click="openEdit(provider)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-white/10 text-white/70 hover:text-cyan-400 hover:border-cyan-400/30 hover:bg-cyan-500/5 transition-all"><Pencil class="w-3.5 h-3.5" /> Edit</button><button @click="confirmDelete(provider)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-red-400/20 text-red-400 hover:bg-red-400/10 transition-all"><Trash2 class="w-3.5 h-3.5" /> Delete</button></div></td>
          </tr>
          <tr v-if="!loading && providers.length === 0"><td colspan="5" class="px-5 py-16 text-center"><div class="flex flex-col items-center justify-center text-white/30"><Server class="w-8 h-8 mb-3 text-white/20" /><p class="text-sm font-medium">No providers yet.</p><p class="text-xs font-mono mt-1">Add a provider to sync products.</p></div></td></tr>
        </tbody>
      </table>
    </div>

    <ConfirmDialog :open="showDeleteConfirm" title="Delete Provider" :message="`Delete '${deletingName}'? This cannot be undone.`" confirm-text="Delete" confirm-variant="destructive" @update:open="showDeleteConfirm = $event" @confirm="handleDelete" @cancel="showDeleteConfirm = false" />

    <ConfirmDialog :open="showTopUp" title="Top Up Provider" message="Enter the amount to add to the provider balance." confirm-text="Top Up" :loading="topUpLoading" @update:open="showTopUp = $event" @confirm="handleTopUp" @cancel="showTopUp = false">
      <template #default>
        <div class="py-4"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">Amount</label><input v-model.number="topUpAmount" type="number" min="1" step="0.01" class="mt-2 w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-cyan-400/50 transition-all" placeholder="Enter amount" /></div>
      </template>
    </ConfirmDialog>
  </div>
</template>
