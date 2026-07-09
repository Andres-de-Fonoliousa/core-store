<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { useUiStore } from '@/stores/uiStore';
import EmptyState from '@/components/EmptyState.vue';
import {
  Users, RefreshCw, Search, X, Eye, ChevronLeft, ChevronRight,
  Wallet, Package, CreditCard, User, ArrowUpDown, Mail, Send, Trash2,
  UserPlus,
} from 'lucide-vue-next';
import SkeletonTable from '@/components/SkeletonTable.vue';

const { t } = useI18n();
const api = useApi();
const toast = useToast();
const ui = useUiStore();

const users = ref<any[]>([]);
const loading = ref(false);
const searchQuery = ref('');
const page = ref(1);
const lastPage = ref(1);
const totalItems = ref(0);

const invitations = ref<any[]>([]);
const showInviteModal = ref(false);
const inviteEmail = ref('');
const inviting = ref(false);

const filteredUsers = computed(() => {
  if (!searchQuery.value.trim()) return users.value;
  const q = searchQuery.value.toLowerCase();
  return users.value.filter(u =>
    u.id?.toString().includes(q) ||
    u.name?.toLowerCase().includes(q) ||
    u.email?.toLowerCase().includes(q)
  );
});

async function loadUsers() {
  loading.value = true;
  try {
    const { data } = await api.get('/admin/users', { params: { page: page.value } });
    users.value = data.data ?? data ?? [];
    page.value = data.current_page ?? 1;
    lastPage.value = data.last_page ?? 1;
    totalItems.value = data.total ?? users.value.length;
  } catch (error) {
    toast.error('فشل تحميل المستخدمين');
  } finally {
    loading.value = false;
  }
}

async function loadInvitations() {
  try {
    const { data } = await api.get('/admin/invitations');
    invitations.value = data.data ?? data ?? [];
  } catch (error) {
    // silently fail
  }
}

async function inviteUser() {
  if (!inviteEmail.value.trim()) return;
  inviting.value = true;
  try {
    await api.post('/admin/invitations', { email: inviteEmail.value });
    toast.success(t('platform.invitations.sent'));
    inviteEmail.value = '';
    showInviteModal.value = false;
    await loadInvitations();
  } catch (error: any) {
    toast.error(error?.response?.data?.message ?? t('platform.invitations.failed'));
  } finally {
    inviting.value = false;
  }
}

async function resendInvitation(invitation: any) {
  try {
    await api.post(`/admin/invitations/${invitation.id}/resend`);
    toast.success(t('platform.invitations.resent'));
  } catch (error: any) {
    toast.error(error?.response?.data?.message ?? t('platform.invitations.failed'));
  }
}

async function revokeInvitation(invitation: any) {
  if (!confirm(t('platform.invitations.revoke') + '?')) return;
  try {
    await api.delete(`/admin/invitations/${invitation.id}`);
    toast.success(t('platform.invitations.revoked'));
    invitations.value = invitations.value.filter((i: any) => i.id !== invitation.id);
  } catch (error: any) {
    toast.error(error?.response?.data?.message ?? t('platform.invitations.failed'));
  }
}

function goPage(p: number) {
  if (p < 1 || p > lastPage.value) return;
  page.value = p;
  loadUsers();
}

ui.setPageTitle('Users');

onMounted(() => {
  loadUsers();
  loadInvitations();
});
</script>

<template>
  <Head title="Users" />

  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">Management</span>
        <h1 class="text-3xl font-bold tracking-tight mt-1">Users</h1>
        <p class="text-sm text-white/40 mt-1">{{ totalItems }} user{{ totalItems !== 1 ? 's' : '' }}</p>
      </div>
      <div class="flex items-center gap-2">
        <button @click="showInviteModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-cyan-400/10 text-cyan-400 border border-cyan-400/20 rounded-xl hover:bg-cyan-400/20 transition-all active:scale-95">
          <UserPlus class="w-3.5 h-3.5" /> {{ t('platform.invitations.invite') }}
        </button>
        <button @click="loadUsers" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all active:scale-95">
          <RefreshCw class="w-3.5 h-3.5" /> Refresh
        </button>
      </div>
    </div>

    <!-- Search -->
    <div class="relative">
      <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
      <input v-model="searchQuery" type="text" placeholder="Search by ID, name, or email..." class="w-full h-11 pl-12 pr-4 bg-white/[0.02] border border-white/10 rounded-xl text-sm text-white placeholder:text-white/25 focus:outline-none focus:border-cyan-400/50 transition-all" />
      <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-red-400 transition-colors">
        <X class="w-3.5 h-3.5" />
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
              <th class="px-5 py-4">Email</th>
              <th class="px-5 py-4">Balance</th>
              <th class="px-5 py-4">Orders</th>
              <th class="px-5 py-4">Deposits</th>
              <th class="px-5 py-4">Joined</th>
              <th class="px-5 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="8" class="px-5 py-10">
                <SkeletonTable :rows="8" :cols="8" />
              </td>
            </tr>

            <tr v-for="user in filteredUsers" :key="user.id" class="border-t border-white/5 hover:bg-white/[0.03] transition-colors">
              <td class="px-5 py-4 font-mono text-white/70">#{{ user.id }}</td>
              <td class="px-5 py-4">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full bg-white/5 border border-white/10 flex items-center justify-center">
                    <User class="w-4 h-4 text-white/50" />
                  </div>
                  <span class="text-white/80 font-medium">{{ user.name ?? 'Unknown' }}</span>
                </div>
              </td>
              <td class="px-5 py-4 text-white/60 font-mono text-xs">{{ user.email }}</td>
              <td class="px-5 py-4 font-mono text-cyan-400 font-semibold">${{ Number(user.balance ?? 0).toFixed(2) }}</td>
              <td class="px-5 py-4">
                <div class="flex items-center gap-1.5 text-white/50">
                  <Package class="w-3.5 h-3.5" />
                  <span class="font-mono text-xs">{{ user.orders_count ?? 0 }}</span>
                </div>
              </td>
              <td class="px-5 py-4">
                <div class="flex items-center gap-1.5 text-white/50">
                  <CreditCard class="w-3.5 h-3.5" />
                  <span class="font-mono text-xs">{{ user.deposits_count ?? 0 }}</span>
                </div>
              </td>
              <td class="px-5 py-4 text-white/40 font-mono text-xs">{{ new Date(user.created_at).toLocaleDateString() }}</td>
              <td class="px-5 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <Link :href="`/admin/users/${user.id}/orders`" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs bg-white/5 text-white/60 border border-white/10 rounded-lg hover:bg-cyan-400/10 hover:text-cyan-400 hover:border-cyan-400/30 transition-all" title="View Orders">
                    <Package class="w-3.5 h-3.5" />
                  </Link>
                  <Link :href="`/admin/users/${user.id}/deposits`" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs bg-white/5 text-white/60 border border-white/10 rounded-lg hover:bg-emerald-400/10 hover:text-emerald-400 hover:border-emerald-400/30 transition-all" title="View Deposits">
                    <Wallet class="w-3.5 h-3.5" />
                  </Link>
                  <Link :href="`/admin/users/${user.id}`" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs bg-white/5 text-white/70 border border-white/10 rounded-lg hover:bg-cyan-400/10 hover:text-cyan-400 hover:border-cyan-400/30 transition-all">
                    <Eye class="w-3.5 h-3.5" />
                  </Link>
                </div>
              </td>
            </tr>

            <tr v-if="!loading && filteredUsers.length === 0">
              <td colspan="8" class="px-5 py-16 text-center">
                <EmptyState :icon="Users" title="No users found" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="lastPage > 1" class="flex items-center justify-center gap-3">
      <button @click="goPage(page - 1)" :disabled="page <= 1" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm rounded-xl border border-white/10 bg-white/[0.02] hover:border-cyan-400/30 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
        <ChevronRight class="w-4 h-4" /> Previous
      </button>
      <div class="flex items-center gap-2">
        <button v-for="p in lastPage" :key="p" @click="goPage(p)" class="w-10 h-10 text-sm rounded-xl border transition-all" :class="page === p ? 'bg-cyan-400/10 text-cyan-400 border-cyan-400/30 font-bold' : 'bg-white/[0.02] text-white/60 border-white/10 hover:border-cyan-400/20'">{{ p }}</button>
      </div>
      <button @click="goPage(page + 1)" :disabled="page >= lastPage" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm rounded-xl border border-white/10 bg-white/[0.02] hover:border-cyan-400/30 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
        Next <ChevronLeft class="w-4 h-4" />
      </button>
    </div>

    <!-- Invitations Section -->
    <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden backdrop-blur-xl">
      <div class="px-5 py-4 border-b border-white/5">
        <h2 class="text-sm font-semibold text-white/80 flex items-center gap-2">
          <Mail class="w-4 h-4 text-cyan-400" /> {{ t('platform.invitations.title') }}
        </h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-white/5 text-left text-xs font-mono uppercase tracking-[0.12em] text-white/40">
            <tr>
              <th class="px-5 py-4">User</th>
              <th class="px-5 py-4">Email</th>
              <th class="px-5 py-4">Role</th>
              <th class="px-5 py-4">Invited</th>
              <th class="px-5 py-4">Status</th>
              <th class="px-5 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="inv in invitations" :key="inv.id" class="border-t border-white/5 hover:bg-white/[0.03] transition-colors">
              <td class="px-5 py-4">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full bg-white/5 border border-white/10 flex items-center justify-center">
                    <User class="w-4 h-4 text-white/50" />
                  </div>
                  <span class="text-white/80 font-medium">{{ inv.user?.name ?? 'Unknown' }}</span>
                </div>
              </td>
              <td class="px-5 py-4 text-white/60 font-mono text-xs">{{ inv.user?.email }}</td>
              <td class="px-5 py-4">
                <span class="text-xs font-mono px-2 py-1 rounded-full bg-white/5 text-white/60 border border-white/10 capitalize">{{ inv.role }}</span>
              </td>
              <td class="px-5 py-4 text-white/40 font-mono text-xs">{{ new Date(inv.invited_at).toLocaleDateString() }}</td>
              <td class="px-5 py-4">
                <span v-if="inv.joined_at" class="text-xs font-mono px-2 py-1 rounded-full bg-emerald-400/10 text-emerald-400 border border-emerald-400/20">{{ t('platform.invitations.statusJoined') }}</span>
                <span v-else class="text-xs font-mono px-2 py-1 rounded-full bg-amber-400/10 text-amber-400 border border-amber-400/20">{{ t('platform.invitations.statusPending') }}</span>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button v-if="!inv.joined_at" @click="resendInvitation(inv)" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs bg-white/5 text-white/60 border border-white/10 rounded-lg hover:bg-cyan-400/10 hover:text-cyan-400 hover:border-cyan-400/30 transition-all" :title="t('platform.invitations.resend')">
                    <Send class="w-3.5 h-3.5" />
                  </button>
                  <button v-if="!inv.joined_at" @click="revokeInvitation(inv)" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs bg-white/5 text-red-400/60 border border-white/10 rounded-lg hover:bg-red-400/10 hover:text-red-400 hover:border-red-400/30 transition-all" :title="t('platform.invitations.revoke')">
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="invitations.length === 0">
              <td colspan="6" class="px-5 py-10 text-center text-white/40 text-sm">{{ t('platform.invitations.noInvitations') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Invite Modal -->
  <Teleport to="body">
    <div v-if="showInviteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showInviteModal = false">
      <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" />
      <div class="relative z-10 w-full max-w-md bg-gray-900 border border-white/10 rounded-2xl p-6 shadow-2xl">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-bold text-white">{{ t('platform.invitations.invite') }}</h3>
          <button @click="showInviteModal = false" class="text-white/40 hover:text-white transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-white/70 mb-2">{{ t('platform.invitations.emailLabel') }}</label>
            <input
              v-model="inviteEmail"
              type="email"
              placeholder="user@example.com"
              class="w-full h-11 px-4 bg-white/[0.02] border border-white/10 rounded-xl text-sm text-white placeholder:text-white/25 focus:outline-none focus:border-cyan-400/50 transition-all"
              @keyup.enter="inviteUser"
            />
          </div>
          <div class="flex items-center gap-3 pt-2">
            <button @click="showInviteModal = false" class="flex-1 px-4 py-2.5 text-sm font-medium bg-white/5 text-white/70 border border-white/10 rounded-xl hover:bg-white/10 transition-all">
              {{ t('platform.invitations.cancel') }}
            </button>
            <button @click="inviteUser" :disabled="inviting || !inviteEmail.trim()" class="flex-1 px-4 py-2.5 text-sm font-semibold bg-cyan-400/10 text-cyan-400 border border-cyan-400/20 rounded-xl hover:bg-cyan-400/20 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
              {{ inviting ? t('platform.invitations.sending') : t('platform.invitations.send') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>