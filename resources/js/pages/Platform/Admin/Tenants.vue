<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { Search, SlidersHorizontal } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

const api = useApi();
const toast = useToast();
const tenants = ref<any>({ data: [] });
const loading = ref(true);
const search = ref('');
const statusFilter = ref('');
const planFilter = ref('');

async function loadTenants() {
  loading.value = true;
  try {
    const params: any = {};
    if (search.value) params.search = search.value;
    if (statusFilter.value) params.status = statusFilter.value;
    if (planFilter.value) params.plan = planFilter.value;
    const { data } = await api.get('/platform/admin/tenants', { params });
    tenants.value = data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
}

onMounted(loadTenants);

async function suspendTenant(id: number) {
  if (!confirm('Suspend this tenant?')) return;
  try {
    await api.post(`/platform/admin/tenants/${id}/suspend`);
    toast.success('Tenant suspended');
    loadTenants();
  } catch (e: any) {
    toast.error(e?.response?.data?.message ?? 'Failed');
  }
}

async function activateTenant(id: number) {
  try {
    await api.post(`/platform/admin/tenants/${id}/activate`);
    toast.success('Tenant activated');
    loadTenants();
  } catch (e: any) {
    toast.error(e?.response?.data?.message ?? 'Failed');
  }
}

async function impersonateTenant(id: number) {
  try {
    const { data } = await api.post(`/platform/admin/tenants/${id}/impersonate`);
    localStorage.setItem('impersonation_token', data.token);
    localStorage.setItem('impersonator_id', String(data.impersonator));
    window.location.href = `/?token=${data.token}`;
  } catch (e: any) {
    toast.error(e?.response?.data?.message ?? 'Failed');
  }
}
</script>

<template>
  <Head title="Tenants" />
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Tenants</h1>
    </div>

    <div class="flex flex-wrap gap-3 mb-6">
      <div class="relative flex-1 min-w-[200px]">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
        <input v-model="search" placeholder="Search tenants..."
          class="w-full bg-gray-900 border border-gray-700 rounded-lg pl-10 pr-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500"
          @input="loadTenants"
        />
      </div>
      <select v-model="statusFilter" @change="loadTenants"
        class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300 focus:outline-none focus:border-indigo-500"
      >
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="trial">Trial</option>
        <option value="suspended">Suspended</option>
      </select>
      <select v-model="planFilter" @change="loadTenants"
        class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300 focus:outline-none focus:border-indigo-500"
      >
        <option value="">All Plans</option>
        <option value="free">Free</option>
        <option value="pro">Pro</option>
        <option value="enterprise">Enterprise</option>
      </select>
    </div>

    <div class="rounded-xl border border-gray-800 bg-gray-900/50 overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-gray-400 border-b border-gray-800 bg-gray-900/80">
            <th class="text-left px-4 py-3">Name</th>
            <th class="text-left px-4 py-3">Plan</th>
            <th class="text-left px-4 py-3">Status</th>
            <th class="text-left px-4 py-3">Users</th>
            <th class="text-left px-4 py-3">Products</th>
            <th class="text-left px-4 py-3">Orders</th>
            <th class="text-left px-4 py-3">Created</th>
            <th class="text-right px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody v-if="!loading">
          <tr v-for="t in tenants.data" :key="t.id" class="border-b border-gray-800/50 hover:bg-gray-800/30">
            <td class="px-4 py-3">
              <Link :href="route('platform.admin.tenants.show', { id: t.id })" class="text-indigo-400 hover:text-indigo-300">
                {{ t.name }}
              </Link>
            </td>
            <td class="px-4 py-3 capitalize">{{ t.plan }}</td>
            <td class="px-4 py-3">
              <span :class="t.status === 'active' ? 'text-emerald-400' : t.status === 'suspended' ? 'text-red-400' : 'text-yellow-400'">
                {{ t.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-300">{{ t.users_count }}</td>
            <td class="px-4 py-3 text-gray-300">{{ t.products_count ?? '-' }}</td>
            <td class="px-4 py-3 text-gray-300">{{ t.orders_count ?? '-' }}</td>
            <td class="px-4 py-3 text-gray-400">{{ t.created_at?.split('T')[0] }}</td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-2">
                <button v-if="t.status !== 'suspended'" @click="suspendTenant(t.id)"
                  class="text-xs px-2 py-1 rounded bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors"
                >
                  Suspend
                </button>
                <button v-if="t.status === 'suspended'" @click="activateTenant(t.id)"
                  class="text-xs px-2 py-1 rounded bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 transition-colors"
                >
                  Activate
                </button>
                <button @click="impersonateTenant(t.id)"
                  class="text-xs px-2 py-1 rounded bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500/20 transition-colors"
                >
                  Impersonate
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!tenants.data.length">
            <td colspan="8" class="px-4 py-8 text-center text-gray-500">No tenants found</td>
          </tr>
        </tbody>
      </table>
      <div v-if="loading" class="text-center py-8 text-gray-500">Loading...</div>
    </div>
  </div>
</template>
