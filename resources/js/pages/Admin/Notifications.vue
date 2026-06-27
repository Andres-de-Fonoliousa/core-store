<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import { useUiStore } from '@/stores/uiStore';
import { Bell, CheckCheck, ChevronLeft, ExternalLink, Archive } from 'lucide-vue-next';

const ui = useUiStore();
const api = useApi();
const toast = useToast();

ui.setPageTitle('Notifications');

interface Notification {
  id: string;
  type: string;
  data: Record<string, any>;
  read_at: string | null;
  created_at: string;
}

const notifications = ref<Notification[]>([]);
const unreadCount = ref(0);
const loading = ref(false);
const pagination = ref<any>(null);

async function loadNotifications(page = 1) {
  loading.value = true;
  try {
    const res = await api.get('/admin/notifications', { params: { page } });
    notifications.value = res.data.data;
    unreadCount.value = res.data.meta?.unread ?? res.data.unread ?? 0;
    pagination.value = res.data.meta ?? null;
  } catch (err) {
    toast.error(parseApiError(err));
  } finally {
    loading.value = false;
  }
}

async function markAsRead(notification: Notification) {
  if (notification.read_at) return;
  try {
    await api.post(`/admin/notifications/${notification.id}/read`);
    notification.read_at = new Date().toISOString();
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  } catch (err) {
    toast.error(parseApiError(err));
  }
}

async function markAllAsRead() {
  try {
    await api.post('/admin/notifications/read-all');
    notifications.value.forEach((n) => (n.read_at = n.read_at ?? new Date().toISOString()));
    unreadCount.value = 0;
    toast.success('All notifications marked as read');
  } catch (err) {
    toast.error(parseApiError(err));
  }
}

function timeAgo(date: string): string {
  const diff = Date.now() - new Date(date).getTime();
  const mins = Math.floor(diff / 60000);
  if (mins < 1) return 'Just now';
  if (mins < 60) return `${mins}m ago`;
  const hrs = Math.floor(mins / 60);
  if (hrs < 24) return `${hrs}h ago`;
  const days = Math.floor(hrs / 24);
  return `${days}d ago`;
}

onMounted(() => loadNotifications());
</script>

<template>
  <Head title="Notifications" />

  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Notifications</h1>
        <p class="text-sm text-white/40 mt-1">System notifications and alerts</p>
      </div>
      <button
        v-if="unreadCount > 0"
        @click="markAllAsRead"
        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-cyan-500/10 border border-cyan-400/20 text-cyan-400 text-sm font-medium hover:bg-cyan-500/20 transition-all"
      >
        <CheckCheck class="w-4 h-4" />
        Mark all as read
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="w-8 h-8 border-2 border-cyan-400/30 border-t-cyan-400 rounded-full animate-spin" />
    </div>

    <!-- Empty -->
    <div v-else-if="notifications.length === 0" class="flex flex-col items-center justify-center py-20 text-white/30">
      <Bell class="w-16 h-16 mb-4 opacity-50" />
      <p class="text-lg font-medium">No notifications yet</p>
      <p class="text-sm mt-1">Notifications will appear here when something needs your attention</p>
    </div>

    <!-- List -->
    <div v-else class="space-y-2">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        @click="markAsRead(notification)"
        class="group relative flex items-start gap-4 p-4 rounded-xl border transition-all duration-200 cursor-pointer"
        :class="notification.read_at ? 'bg-white/[0.02] border-white/5' : 'bg-cyan-500/5 border-cyan-400/20'"
      >
        <!-- Unread indicator -->
        <div v-if="!notification.read_at" class="absolute top-4 right-4 w-2 h-2 rounded-full bg-cyan-400" />

        <!-- Icon -->
        <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center" :class="notification.read_at ? 'bg-white/5' : 'bg-cyan-500/10'">
          <Bell class="w-5 h-5" :class="notification.read_at ? 'text-white/30' : 'text-cyan-400'" />
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium" :class="notification.read_at ? 'text-white/60' : 'text-white'">
            {{ notification.data.message || 'No details' }}
          </p>
          <p class="text-xs font-mono text-white/30 mt-1">{{ timeAgo(notification.created_at) }}</p>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && pagination.last_page > 1" class="flex items-center justify-center gap-2 pt-4">
      <button
        v-for="page in pagination.last_page"
        :key="page"
        @click="loadNotifications(page)"
        class="w-10 h-10 rounded-xl text-sm font-medium border transition-all"
        :class="page === pagination.current_page ? 'bg-cyan-500/10 border-cyan-400/20 text-cyan-400' : 'bg-white/[0.02] border-white/5 text-white/40 hover:text-white hover:border-white/20'"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>
