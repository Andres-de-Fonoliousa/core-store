<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import { useUiStore } from '@/stores/uiStore';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import {
  Banknote, Plus, Pencil, Trash2, Save, X,
  RefreshCw, Wallet,
} from 'lucide-vue-next';

const api = useApi();
const toast = useToast();
const ui = useUiStore();

const addresses = ref<any[]>([]);
const methods = ref<any[]>([]);
const loading = ref(false);
const saving = ref(false);
const showForm = ref(false);
const editingId = ref<number | null>(null);

const formMethodId = ref<number | null>(null);
const formLabel = ref('');
const formMeta = ref('');
const formActive = ref(true);
const formSort = ref(0);

const showDeleteConfirm = ref(false);
const deletingId = ref<number | null>(null);
const deletingLabel = ref('');

const methodMetaFields: Record<string, { label: string; key: string; placeholder: string }[]> = {
  manual_screenshot: [
    { label: 'اسم البنك', key: 'bank_name', placeholder: 'بنك الراجحي' },
    { label: 'رقم الحساب', key: 'account_number', placeholder: 'SA0000000000000' },
    { label: 'رقم الإيبان (IBAN)', key: 'iban', placeholder: 'SA0000000000000000000' },
    { label: 'اسم صاحب الحساب', key: 'account_holder', placeholder: 'الاسم الكامل' },
  ],
  sham_cash: [
    { label: 'العنوان (Address Code)', key: 'address_code', placeholder: '251aw...' },
    { label: 'معرف العملة', key: 'currency_id', placeholder: '1 (USD)' },
  ],
};

ui.setPageTitle('Payment Addresses');

async function loadData() {
  loading.value = true;
  try {
    const [addrRes, methRes] = await Promise.all([
      api.get('/admin/payment-addresses'),
      api.get('/payment-methods'),
    ]);
    addresses.value = addrRes.data.data ?? addrRes.data ?? [];
    methods.value = methRes.data.data ?? methRes.data ?? [];
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    loading.value = false;
  }
}

function openCreate() {
  editingId.value = null;
  formMethodId.value = methods.value[0]?.id ?? null;
  formLabel.value = '';
  formMeta.value = '{}';
  formActive.value = true;
  formSort.value = 0;
  showForm.value = true;
}

function openEdit(addr: any) {
  editingId.value = addr.id;
  formMethodId.value = addr.payment_method_id;
  formLabel.value = addr.label;
  formMeta.value = JSON.stringify(addr.meta, null, 2);
  formActive.value = addr.is_active;
  formSort.value = addr.sort;
  showForm.value = true;
}

function getMethodCode(id: number): string {
  return methods.value.find(m => m.id === id)?.code ?? '';
}

function buildDefaultMeta() {
  const code = getMethodCode(formMethodId.value!);
  const fields = methodMetaFields[code];
  if (!fields) return '{}';
  const meta: Record<string, string> = {};
  for (const f of fields) {
    meta[f.key] = '';
  }
  return JSON.stringify(meta, null, 2);
}

function onMethodChange() {
  if (!editingId.value) {
    formMeta.value = buildDefaultMeta();
  }
}

async function save() {
  let meta: Record<string, any>;
  try {
    meta = JSON.parse(formMeta.value);
  } catch {
    toast.error('Invalid JSON in meta field');
    return;
  }

  saving.value = true;
  try {
    const payload = {
      payment_method_id: formMethodId.value,
      label: formLabel.value,
      meta,
      is_active: formActive.value,
      sort: formSort.value,
    };

    if (editingId.value) {
      await api.put(`/admin/payment-addresses/${editingId.value}`, payload);
      toast.success('Address updated');
    } else {
      await api.post('/admin/payment-addresses', payload);
      toast.success('Address created');
    }

    showForm.value = false;
    await loadData();
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    saving.value = false;
  }
}

function confirmDelete(addr: any) {
  deletingId.value = addr.id;
  deletingLabel.value = addr.label;
  showDeleteConfirm.value = true;
}

async function doDelete() {
  if (!deletingId.value) return;
  try {
    await api.delete(`/admin/payment-addresses/${deletingId.value}`);
    toast.success('Address deleted');
    showDeleteConfirm.value = false;
    await loadData();
  } catch (error) {
    toast.error(parseApiError(error));
  }
}

onMounted(loadData);
</script>

<template>
  <Head title="Payment Addresses" />
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">Finance</span>
        <h1 class="text-3xl font-bold tracking-tight mt-1">Payment Addresses</h1>
        <p class="text-sm text-white/40 mt-1">Manage deposit addresses for each payment method.</p>
      </div>
      <div class="flex gap-2">
        <button @click="loadData" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all active:scale-95">
          <RefreshCw class="w-3.5 h-3.5" /> Refresh
        </button>
        <button @click="openCreate" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 transition-all active:scale-95">
          <Plus class="w-3.5 h-3.5" /> Add Address
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden backdrop-blur-xl">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-white/5 text-left text-xs font-mono uppercase tracking-[0.12em] text-white/40">
            <tr>
              <th class="px-5 py-4">Method</th>
              <th class="px-5 py-4">Label</th>
              <th class="px-5 py-4">Meta</th>
              <th class="px-5 py-4">Active</th>
              <th class="px-5 py-4">Sort</th>
              <th class="px-5 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="6" class="px-5 py-10 text-center text-white/30 font-mono text-sm">
                <div class="flex items-center justify-center gap-2">
                  <div class="w-4 h-4 border-2 border-cyan-400/30 border-t-cyan-400 rounded-full animate-spin" />
                  Loading...
                </div>
              </td>
            </tr>

            <tr v-for="addr in addresses" :key="addr.id" class="border-t border-white/5 hover:bg-white/[0.03] transition-colors">
              <td class="px-5 py-4">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-mono"
                  :class="addr.method?.code === 'sham_cash' ? 'bg-purple-500/10 text-purple-400 border border-purple-400/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-400/20'">
                  <Wallet class="w-3 h-3" />
                  {{ addr.method?.name ?? addr.payment_method_id }}
                </span>
              </td>
              <td class="px-5 py-4 font-medium text-white/80">{{ addr.label }}</td>
              <td class="px-5 py-4">
                <pre class="text-xs text-white/40 font-mono max-w-[200px] truncate">{{ JSON.stringify(addr.meta) }}</pre>
              </td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[11px] font-semibold rounded-full"
                  :class="addr.is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-white/5 text-white/30'">
                  {{ addr.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-5 py-4 font-mono text-white/40">{{ addr.sort }}</td>
              <td class="px-5 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <button @click="openEdit(addr)" class="p-2 rounded-lg text-white/30 hover:text-cyan-400 hover:bg-white/5 transition-all">
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button @click="confirmDelete(addr)" class="p-2 rounded-lg text-white/30 hover:text-red-400 hover:bg-white/5 transition-all">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!loading && addresses.length === 0">
              <td colspan="6" class="px-5 py-16 text-center">
                <div class="flex flex-col items-center justify-center text-white/30">
                  <Banknote class="w-8 h-8 mb-3 text-white/20" />
                  <p class="text-sm font-medium">No payment addresses configured.</p>
                  <p class="text-xs font-mono mt-1">Click "Add Address" to create one.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Form Modal -->
    <Teleport to="body">
      <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="showForm" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" @click.self="showForm = false">
          <div class="relative max-w-lg w-full">
            <div class="bg-[#0a0a0d] border border-white/10 rounded-2xl overflow-hidden backdrop-blur-xl">
              <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between">
                <h2 class="text-lg font-semibold">{{ editingId ? 'Edit Address' : 'Add Address' }}</h2>
                <button @click="showForm = false" class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-white/60 hover:text-white hover:bg-white/10 transition-all">
                  <X class="w-4 h-4" />
                </button>
              </div>

              <div class="px-6 py-5 space-y-4">
                <!-- Payment Method -->
                <div class="space-y-2">
                  <label class="text-xs font-mono text-white/60 uppercase tracking-wider">Payment Method</label>
                  <select v-model.number="formMethodId" @change="onMethodChange"
                    class="w-full h-11 bg-white/[0.02] border border-white/10 rounded-xl px-4 text-sm text-white focus:outline-none focus:border-cyan-400/50 transition-all">
                    <option v-for="m in methods" :key="m.id" :value="m.id" class="bg-[#0a0a0d]">{{ m.name }}</option>
                  </select>
                </div>

                <!-- Label -->
                <div class="space-y-2">
                  <label class="text-xs font-mono text-white/60 uppercase tracking-wider">Label</label>
                  <input v-model="formLabel" type="text"
                    class="w-full h-11 bg-white/[0.02] border border-white/10 rounded-xl px-4 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-cyan-400/50 transition-all"
                    placeholder="e.g. Bank name or Wallet address" />
                </div>

                <!-- Meta JSON -->
                <div class="space-y-2">
                  <label class="text-xs font-mono text-white/60 uppercase tracking-wider">Meta (JSON)</label>
                  <textarea v-model="formMeta" rows="6"
                    class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-xs font-mono text-white/70 placeholder:text-white/20 focus:outline-none focus:border-cyan-400/50 transition-all resize-none"
                    placeholder='{"key": "value"}' />
                  <p v-if="formMethodId" class="text-[11px] text-white/30 font-mono">
                    Expected fields for <strong class="text-cyan-400">{{ getMethodCode(formMethodId!) }}</strong>:
                    <span class="text-cyan-400/70">{{ methodMetaFields[getMethodCode(formMethodId!)]?.map(f => f.key).join(', ') ?? 'free form' }}</span>
                  </p>
                </div>

                <!-- Active + Sort -->
                <div class="flex gap-4">
                  <div class="flex items-center gap-3">
                    <input v-model="formActive" type="checkbox" id="addr-active"
                      class="w-4 h-4 rounded border-white/10 bg-white/[0.02] text-cyan-400 focus:ring-cyan-400" />
                    <label for="addr-active" class="text-xs font-mono text-white/60">Active</label>
                  </div>
                  <div class="space-y-1">
                    <label class="text-xs font-mono text-white/60 uppercase tracking-wider">Sort</label>
                    <input v-model.number="formSort" type="number" min="0"
                      class="w-20 h-9 bg-white/[0.02] border border-white/10 rounded-lg px-3 text-sm text-white text-center focus:outline-none focus:border-cyan-400/50 transition-all" />
                  </div>
                </div>
              </div>

              <div class="px-6 py-4 border-t border-white/5 flex justify-end gap-3">
                <button @click="showForm = false" class="px-5 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-white/20 transition-all">
                  Cancel
                </button>
                <button @click="save" :disabled="saving"
                  class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 transition-all active:scale-95 disabled:opacity-50">
                  <Save class="w-3.5 h-3.5" />
                  {{ saving ? 'Saving...' : 'Save' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Delete Confirm -->
    <ConfirmDialog
      :open="showDeleteConfirm"
      title="Delete Address"
      :message="`Delete address \"${deletingLabel}\"? This cannot be undone.`"
      confirm-text="Delete"
      confirm-variant="destructive"
      @update:open="showDeleteConfirm = $event"
      @confirm="doDelete"
      @cancel="showDeleteConfirm = false"
    />
  </div>
</template>
