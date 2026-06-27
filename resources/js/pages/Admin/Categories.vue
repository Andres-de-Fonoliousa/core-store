<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import { useUiStore } from '@/stores/uiStore';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import {
  Tag, Plus, Pencil, Trash2, X, Save, Image as ImageIcon,
  RefreshCw, Upload,
} from 'lucide-vue-next';

const api = useApi();
const toast = useToast();
const ui = useUiStore();

const categories = ref<any[]>([]);
const loading = ref(false);
const saving = ref(false);
const showForm = ref(false);
const editingId = ref<number | null>(null);
const formName = ref('');
const formImage = ref('');
const formStatus = ref('active');
const showDeleteConfirm = ref(false);
const deletingId = ref<number | null>(null);
const deletingName = ref('');
const uploading = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const imageVersion = ref(0);

ui.setPageTitle('Categories');

async function loadCategories() {
  loading.value = true;
  try {
    const { data } = await api.get('/admin/categories', { params: { status: 'all' } });
    categories.value = data.data ?? data ?? [];
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    loading.value = false;
  }
}

function openCreate() {
  editingId.value = null;
  formName.value = '';
  formImage.value = '';
  formStatus.value = 'active';
  showForm.value = true;
}

function openEdit(category: any) {
  editingId.value = category.id;
  formName.value = category.name;
  formImage.value = category.image || '';
  formStatus.value = category.status;
  showForm.value = true;
}

async function saveCategory() {
  if (!formName.value.trim()) { toast.error('Name is required.'); return; }
  saving.value = true;
  try {
    const payload = { name: formName.value, image: formImage.value || null, status: formStatus.value };
    if (editingId.value) {
      await api.put(`/admin/categories/${editingId.value}`, payload);
      toast.success('Category updated.');
    } else {
      await api.post('/admin/categories', payload);
      toast.success('Category created.');
    }
    showForm.value = false;
    await loadCategories();
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    saving.value = false;
  }
}

function confirmDelete(category: any) {
  deletingId.value = category.id;
  deletingName.value = category.name;
  showDeleteConfirm.value = true;
}

async function handleDelete() {
  if (!deletingId.value) return;
  try {
    await api.delete(`/admin/categories/${deletingId.value}`);
    toast.success('Category deleted.');
    showDeleteConfirm.value = false;
    await loadCategories();
  } catch (error) {
    toast.error(parseApiError(error));
  }
}

function triggerFileInput() {
  fileInput.value?.click();
}

async function handleFileSelected(event: Event) {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file || !editingId.value) return;
  uploading.value = true;
  try {
    const formData = new FormData();
    formData.append('image', file);
    const { data } = await api.post(`/admin/categories/${editingId.value}/image`, formData);
    formImage.value = data.path;
    imageVersion.value++;
    toast.success('Image uploaded.');
    await loadCategories();
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    uploading.value = false;
    input.value = '';
  }
}

async function toggleStatus(category: any) {
  const next = category.status === 'active' ? 'inactive' : 'active';
  try {
      await api.put(`/admin/categories/${category.id}`, { status: next });
    category.status = next;
    toast.success('Status updated.');
  } catch (error) {
    toast.error(parseApiError(error));
  }
}

onMounted(loadCategories);
</script>

<template>
  <Head title="Categories" />
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">Catalog</span>
        <h1 class="text-3xl font-bold tracking-tight mt-1">Categories</h1>
        <p class="text-sm text-white/40 mt-1">Organize your product catalog.</p>
      </div>
      <div class="flex gap-3">
        <button @click="loadCategories" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all active:scale-95">
          <RefreshCw class="w-3.5 h-3.5" /> Refresh
        </button>
        <button @click="openCreate" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 hover:shadow-[0_0_25px_-5px_rgba(34,211,238,0.5)] transition-all active:scale-95">
          <Plus class="w-4 h-4" /> Create
        </button>
      </div>
    </div>

    <div v-if="showForm" class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl space-y-5">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-white">{{ editingId ? 'Edit Category' : 'New Category' }}</h2>
        <button @click="showForm = false" class="text-white/40 hover:text-white transition-colors"><X class="w-5 h-5" /></button>
      </div>
      <div class="grid gap-4 sm:grid-cols-3">
        <div class="space-y-2">
          <label class="text-xs font-mono text-white/60 uppercase tracking-wider">Name</label>
          <input v-model="formName" type="text" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-cyan-400/50 transition-all" placeholder="Category name" />
        </div>
        <div class="space-y-2">
          <label class="text-xs font-mono text-white/60 uppercase tracking-wider">Image</label>
          <div class="flex items-center gap-3">
            <div class="relative h-16 w-16 rounded-xl overflow-hidden border border-white/10 flex-shrink-0 bg-white/5">
              <img v-if="formImage" :src="`/${formImage}?v=${imageVersion}`" class="h-full w-full object-cover" />
              <div v-else class="h-full w-full flex items-center justify-center text-white/20"><ImageIcon class="w-5 h-5" /></div>
            </div>
            <div class="flex-1 min-w-0">
              <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="handleFileSelected" />
              <button v-if="editingId" @click="triggerFileInput" :disabled="uploading" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all active:scale-95 disabled:opacity-60">
                <Upload class="w-3.5 h-3.5" /> {{ uploading ? 'Uploading...' : 'Upload' }}
              </button>
              <p v-if="!editingId" class="text-xs text-white/30 font-mono">Save the category first, then upload an image.</p>
            </div>
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-xs font-mono text-white/60 uppercase tracking-wider">Status</label>
          <select v-model="formStatus" class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-cyan-400/50 transition-all appearance-none">
            <option value="active" class="bg-[#0a0a0c]">Active</option>
            <option value="inactive" class="bg-[#0a0a0c]">Inactive</option>
          </select>
        </div>
      </div>
      <div class="flex gap-3">
        <button @click="saveCategory" :disabled="saving" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 transition-all active:scale-95 disabled:opacity-60">
          <Save class="w-4 h-4" /> {{ saving ? 'Saving...' : 'Save' }}
        </button>
        <button @click="showForm = false" class="px-5 py-2.5 text-sm font-medium text-white/70 border border-white/10 rounded-xl hover:bg-white/5 hover:text-white transition-all">Cancel</button>
      </div>
    </div>

    <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden backdrop-blur-xl">
      <table class="min-w-full text-sm">
        <thead class="bg-white/5 text-left text-xs font-mono uppercase tracking-[0.12em] text-white/40">
          <tr><th class="px-5 py-4">Name</th><th class="px-5 py-4">Image</th><th class="px-5 py-4">Status</th><th class="px-5 py-4 text-right">Actions</th></tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="4" class="px-5 py-10 text-center text-white/30 font-mono text-sm">
              <div class="flex items-center justify-center gap-2"><div class="w-4 h-4 border-2 border-cyan-400/30 border-t-cyan-400 rounded-full animate-spin" /> Loading...</div>
            </td>
          </tr>
          <tr v-for="cat in categories" :key="cat.id" class="border-t border-white/5 hover:bg-white/[0.03] transition-colors">
            <td class="px-5 py-4 font-medium text-white">{{ cat.name }}</td>
            <td class="px-5 py-4">
              <img v-if="cat.image" :src="'/' + cat.image + '?v=' + imageVersion" class="h-10 w-10 rounded-lg object-cover border border-white/10" />
              <div v-else class="h-10 w-10 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-white/20"><ImageIcon class="w-4 h-4" /></div>
            </td>
            <td class="px-5 py-4">
              <button @click="toggleStatus(cat)" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-mono font-semibold transition-all border" :class="cat.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-400/20' : 'bg-white/5 text-white/40 border-white/10'">
                <span class="w-1.5 h-1.5 rounded-full" :class="cat.status === 'active' ? 'bg-emerald-400 animate-pulse' : 'bg-white/30'" /> {{ cat.status }}
              </button>
            </td>
            <td class="px-5 py-4 text-right">
              <div class="flex justify-end gap-2">
                <button @click="openEdit(cat)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-white/10 text-white/70 hover:text-cyan-400 hover:border-cyan-400/30 hover:bg-cyan-500/5 transition-all"><Pencil class="w-3.5 h-3.5" /> Edit</button>
                <button @click="confirmDelete(cat)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-red-400/20 text-red-400 hover:bg-red-400/10 transition-all"><Trash2 class="w-3.5 h-3.5" /> Delete</button>
              </div>
            </td>
          </tr>
          <tr v-if="!loading && categories.length === 0">
            <td colspan="4" class="px-5 py-16 text-center">
              <div class="flex flex-col items-center justify-center text-white/30"><Tag class="w-8 h-8 mb-3 text-white/20" /><p class="text-sm font-medium">No categories yet.</p><p class="text-xs font-mono mt-1">Create your first category.</p></div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ConfirmDialog :open="showDeleteConfirm" title="Delete Category" :message="`Delete '${deletingName}'? This cannot be undone.`" confirm-text="Delete" confirm-variant="destructive" @update:open="showDeleteConfirm = $event" @confirm="handleDelete" @cancel="showDeleteConfirm = false" />
  </div>
</template>
