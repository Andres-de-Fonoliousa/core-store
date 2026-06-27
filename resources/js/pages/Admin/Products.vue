<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import { useUiStore } from '@/stores/uiStore';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import {
  Box, Plus, Pencil, Trash2, Save, X, Image as ImageIcon,
  Layers, Hash, Zap, RefreshCw,
} from 'lucide-vue-next';

const api = useApi();
const toast = useToast();
const ui = useUiStore();

const products = ref<any[]>([]);
const categories = ref<any[]>([]);
const providers = ref<any[]>([]);
const loading = ref(false);
const saving = ref(false);
const showForm = ref(false);
const editingId = ref<number | null>(null);
const form = ref({ name: '', provider_id: null as number | null, category_id: null as number | null, cost_price: 0, price: 0, external_id: '', params: [] as string[], qty_values: [] as number[], is_auto: true, status: 'active', image: '', manual_price: false });
const showDeleteConfirm = ref(false);
const deletingId = ref<number | null>(null);
const deletingName = ref('');

ui.setPageTitle('Products');

async function loadProducts() {
  loading.value = true;
  try {
    const { data } = await api.get('/admin/products');
    products.value = data.data ?? data ?? [];
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    loading.value = false;
  }
}

async function loadFormData() {
  try {
    const [catRes, provRes] = await Promise.all([
      api.get('/admin/categories'),
      api.get('/admin/providers'),
    ]);
    categories.value = catRes.data.data ?? catRes.data ?? [];
    providers.value = provRes.data.data ?? provRes.data ?? [];
  } catch (error) {}
}

function resetForm() {
  form.value = { name: '', provider_id: null, category_id: null, cost_price: 0, price: 0, external_id: '', params: [], qty_values: [], is_auto: true, status: 'active', image: '', manual_price: false };
}

function openCreate() { editingId.value = null; resetForm(); showForm.value = true; }
function openEdit(product: any) {
  editingId.value = product.id;
  form.value = { name: product.name, provider_id: product.provider?.id ?? null, category_id: product.category?.id ?? null, cost_price: product.cost_price, price: product.price, external_id: product.external_id, params: product.params ?? [], qty_values: product.qty_values ?? [], is_auto: product.is_auto, status: product.status, image: product.image ?? '', manual_price: true };
  showForm.value = true;
}

function addParam() { form.value.params.push(''); }
function removeParam(index: number) { form.value.params.splice(index, 1); }
function addQtyValue() { form.value.qty_values.push(0); }
function removeQtyValue(index: number) { form.value.qty_values.splice(index, 1); }
function autoCalcPrice() { if (!form.value.manual_price) form.value.price = Number((form.value.cost_price * 1.15).toFixed(2)); }

async function saveProduct() {
  if (!form.value.name.trim() || !form.value.provider_id || !form.value.category_id) {
    toast.error('Please fill in required fields.'); return;
  }
  saving.value = true;
  try {
    const payload = { name: form.value.name, provider_id: form.value.provider_id, category_id: form.value.category_id, cost_price: Number(form.value.cost_price), price: Number(form.value.price), external_id: form.value.external_id, params: form.value.params.filter((p) => p.trim()), qty_values: form.value.qty_values.filter((v) => v > 0).map(Number), is_auto: form.value.is_auto, status: form.value.status, image: form.value.image || null };
    if (editingId.value) {
      await api.put(`/admin/products/${editingId.value}`, payload);
      toast.success('Product updated.');
    } else {
      await api.post('/admin/products', payload);
      toast.success('Product created.');
    }
    showForm.value = false;
    await loadProducts();
  } catch (error: any) {
    toast.error(parseApiError(error));
  } finally {
    saving.value = false;
  }
}

function confirmDelete(product: any) { deletingId.value = product.id; deletingName.value = product.name; showDeleteConfirm.value = true; }
async function handleDelete() {
  if (!deletingId.value) return;
  try {
    await api.delete(`/admin/products/${deletingId.value}`);
    toast.success('Product deleted.'); showDeleteConfirm.value = false; await loadProducts();
  } catch (error) { toast.error(parseApiError(error)); }
}

onMounted(() => { loadProducts(); loadFormData(); });
</script>

<template>
  <Head title="Products" />
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">Catalog</span>
        <h1 class="text-3xl font-bold tracking-tight mt-1">Products</h1>
        <p class="text-sm text-white/40 mt-1">Manage your digital inventory.</p>
      </div>
      <div class="flex gap-3">
        <button @click="loadProducts" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all active:scale-95">
          <RefreshCw class="w-3.5 h-3.5" /> Refresh
        </button>
        <button @click="openCreate" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 hover:shadow-[0_0_25px_-5px_rgba(34,211,238,0.5)] transition-all active:scale-95">
          <Plus class="w-4 h-4" /> Create
        </button>
      </div>
    </div>

    <div v-if="showForm" class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-white">{{ editingId ? 'Edit Product' : 'New Product' }}</h2>
        <button @click="showForm = false" class="text-white/40 hover:text-white transition-colors"><X class="w-5 h-5" /></button>
      </div>
      <div class="grid gap-4 sm:grid-cols-3">
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">Name</label><input v-model="form.name" type="text" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-cyan-400/50 transition-all" placeholder="Product name" /></div>
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">Category</label><select v-model="form.category_id" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-400/50 transition-all appearance-none"><option :value="null" class="bg-[#0a0a0c]">Select category</option><option v-for="cat in categories" :key="cat.id" :value="cat.id" class="bg-[#0a0a0c]">{{ cat.name }}</option></select></div>
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">Provider</label><select v-model="form.provider_id" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-400/50 transition-all appearance-none"><option :value="null" class="bg-[#0a0a0c]">Select provider</option><option v-for="prov in providers" :key="prov.id" :value="prov.id" class="bg-[#0a0a0c]">{{ prov.name }}</option></select></div>
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">Cost Price</label><input v-model.number="form.cost_price" type="number" min="0" step="0.01" @input="autoCalcPrice" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-400/50 transition-all" /></div>
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">Selling Price</label><input v-model.number="form.price" type="number" min="0" step="0.01" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-400/50 transition-all" /><label class="flex items-center gap-2 text-xs text-white/40 cursor-pointer"><input type="checkbox" v-model="form.manual_price" class="rounded border-white/20 bg-white/5 text-cyan-400 focus:ring-cyan-400/50" /><span class="font-mono">Manual price</span></label></div>
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">External ID</label><input v-model="form.external_id" type="text" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-cyan-400/50 transition-all" placeholder="Provider product ID" /></div>
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">Image URL</label><input v-model="form.image" type="text" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-cyan-400/50 transition-all" placeholder="https://..." /></div>
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">Status</label><select v-model="form.status" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-400/50 transition-all appearance-none"><option value="active" class="bg-[#0a0a0c]">Active</option><option value="inactive" class="bg-[#0a0a0c]">Inactive</option></select></div>
        <div class="space-y-2"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">Delivery</label><select v-model="form.is_auto" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-400/50 transition-all appearance-none"><option :value="true" class="bg-[#0a0a0c]">Auto Delivery</option><option :value="false" class="bg-[#0a0a0c]">Manual Delivery</option></select></div>
      </div>

      <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4 space-y-3">
        <label class="text-xs font-mono text-white/60 uppercase tracking-wider flex items-center gap-2"><Layers class="w-3.5 h-3.5 text-cyan-400" /> Parameters</label>
        <div v-for="(param, idx) in form.params" :key="idx" class="flex gap-2"><input v-model="form.params[idx]" type="text" class="flex-1 bg-white/[0.02] border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-cyan-400/50 transition-all" :placeholder="`Param ${idx + 1}`" /><button @click="removeParam(idx)" class="px-3 py-2 text-xs font-medium rounded-lg border border-red-400/20 text-red-400 hover:bg-red-400/10 transition-all">Remove</button></div>
        <button @click="addParam" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium rounded-lg border border-white/10 text-white/70 hover:text-cyan-400 hover:border-cyan-400/30 hover:bg-cyan-500/5 transition-all"><Plus class="w-3.5 h-3.5" /> Add Param</button>
      </div>

      <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4 space-y-3">
        <label class="text-xs font-mono text-white/60 uppercase tracking-wider flex items-center gap-2"><Hash class="w-3.5 h-3.5 text-purple-400" /> Quantity Values</label>
        <div v-for="(qv, idx) in form.qty_values" :key="idx" class="flex gap-2"><input v-model.number="form.qty_values[idx]" type="number" min="0" class="w-32 bg-white/[0.02] border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-400/50 transition-all" :placeholder="`Value ${idx + 1}`" /><button @click="removeQtyValue(idx)" class="px-3 py-2 text-xs font-medium rounded-lg border border-red-400/20 text-red-400 hover:bg-red-400/10 transition-all">Remove</button></div>
        <button @click="addQtyValue" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium rounded-lg border border-white/10 text-white/70 hover:text-purple-400 hover:border-purple-400/30 hover:bg-purple-500/5 transition-all"><Plus class="w-3.5 h-3.5" /> Add Qty Value</button>
      </div>

      <div class="flex gap-3 pt-2">
        <button @click="saveProduct" :disabled="saving" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 transition-all active:scale-95 disabled:opacity-60"><Save class="w-4 h-4" /> {{ saving ? 'Saving...' : 'Save' }}</button>
        <button @click="showForm = false" class="px-5 py-2.5 text-sm font-medium text-white/70 border border-white/10 rounded-xl hover:bg-white/5 hover:text-white transition-all">Cancel</button>
      </div>
    </div>

    <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden backdrop-blur-xl">
      <table class="min-w-full text-sm">
        <thead class="bg-white/5 text-left text-xs font-mono uppercase tracking-[0.12em] text-white/40">
          <tr><th class="px-5 py-4">Name</th><th class="px-5 py-4">Provider</th><th class="px-5 py-4">Category</th><th class="px-5 py-4">Cost</th><th class="px-5 py-4">Price</th><th class="px-5 py-4">Auto</th><th class="px-5 py-4">Status</th><th class="px-5 py-4 text-right">Actions</th></tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="8" class="px-5 py-10 text-center text-white/30 font-mono text-sm"><div class="flex items-center justify-center gap-2"><div class="w-4 h-4 border-2 border-cyan-400/30 border-t-cyan-400 rounded-full animate-spin" /> Loading...</div></td></tr>
          <tr v-for="product in products" :key="product.id" class="border-t border-white/5 hover:bg-white/[0.03] transition-colors">
            <td class="px-5 py-4"><div class="flex items-center gap-3"><img v-if="product.image" :src="product.image" class="h-9 w-9 rounded-lg object-cover border border-white/10" /><div v-else class="h-9 w-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-white/20"><ImageIcon class="w-4 h-4" /></div><span class="font-medium text-white">{{ product.name }}</span></div></td>
            <td class="px-5 py-4 text-white/50">{{ product.provider?.name }}</td>
            <td class="px-5 py-4 text-white/50">{{ product.category?.name }}</td>
            <td class="px-5 py-4 font-mono text-white/60">${{ Number(product.cost_price).toFixed(2) }}</td>
            <td class="px-5 py-4 font-mono text-cyan-400 font-semibold">${{ Number(product.price).toFixed(2) }}</td>
            <td class="px-5 py-4"><span class="inline-flex items-center gap-1 text-xs font-mono" :class="product.is_auto ? 'text-emerald-400' : 'text-white/30'"><Zap class="w-3 h-3" :class="product.is_auto ? 'fill-emerald-400/20' : ''" /> {{ product.is_auto ? 'Yes' : 'No' }}</span></td>
            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-[11px] font-mono font-semibold border" :class="product.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-400/20' : 'bg-white/5 text-white/40 border-white/10'">{{ product.status }}</span></td>
            <td class="px-5 py-4 text-right"><div class="flex justify-end gap-2"><button @click="openEdit(product)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-white/10 text-white/70 hover:text-cyan-400 hover:border-cyan-400/30 hover:bg-cyan-500/5 transition-all"><Pencil class="w-3.5 h-3.5" /> Edit</button><button @click="confirmDelete(product)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-red-400/20 text-red-400 hover:bg-red-400/10 transition-all"><Trash2 class="w-3.5 h-3.5" /> Delete</button></div></td>
          </tr>
          <tr v-if="!loading && products.length === 0"><td colspan="8" class="px-5 py-16 text-center"><div class="flex flex-col items-center justify-center text-white/30"><Box class="w-8 h-8 mb-3 text-white/20" /><p class="text-sm font-medium">No products found.</p><p class="text-xs font-mono mt-1">Create your first product.</p></div></td></tr>
        </tbody>
      </table>
    </div>

    <ConfirmDialog :open="showDeleteConfirm" title="Delete Product" :message="`Delete '${deletingName}'? This cannot be undone.`" confirm-text="Delete" confirm-variant="destructive" @update:open="showDeleteConfirm = $event" @confirm="handleDelete" @cancel="showDeleteConfirm = false" />
  </div>
</template>
