<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import {
  Bell, CheckCheck, Package, Wallet, CreditCard, Info,
  ExternalLink, ChevronLeft, Loader2,
} from 'lucide-vue-next';

const api = useApi();
const toast = useToast();

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
    const res = await api.get('/notifications', { params: { page } });
    notifications.value = res.data.data;
    unreadCount.value = res.data.meta?.unread ?? 0;
    pagination.value = res.data.meta ?? null;
  } catch (err) {
    toast.error(parseApiError(err));
  } finally {
    loading.value = false;
  }
}

async function markAsRead(notif: Notification) {
  if (notif.read_at) return;
  try {
    await api.post(`/notifications/${notif.id}/read`);
    notif.read_at = new Date().toISOString();
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  } catch (err) {
    toast.error(parseApiError(err));
  }
}

async function markAllAsRead() {
  try {
    await api.post('/notifications/read-all');
    notifications.value.forEach(n => n.read_at = n.read_at ?? new Date().toISOString());
    unreadCount.value = 0;
    toast.success('تم تحديد الكل كمقروء');
  } catch (err) {
    toast.error(parseApiError(err));
  }
}

function notifIcon(type: string) {
  if (type.includes('Order')) return Package;
  if (type.includes('Deposit')) return Wallet;
  if (type.includes('Transaction')) return CreditCard;
  return Info;
}

function notifData(notif: Notification) {
  const data = typeof notif.data === 'string' ? JSON.parse(notif.data) : notif.data;
  return {
    title: data?.title ?? data?.message ?? 'إشعار',
    body: data?.body ?? data?.message ?? '',
    url: data?.url ?? null,
  };
}

function timeAgo(date: string): string {
  const diff = Date.now() - new Date(date).getTime();
  const mins = Math.floor(diff / 60000);
  if (mins < 1) return 'الآن';
  if (mins < 60) return `منذ ${mins} د`;
  const hrs = Math.floor(mins / 60);
  if (hrs < 24) return `منذ ${hrs} س`;
  const days = Math.floor(hrs / 24);
  return `منذ ${days} ي`;
}

onMounted(() => loadNotifications());
</script>

<template>
  <Head title="الإشعارات" />

  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">الإشعارات</span>
        <h1 class="text-2xl font-bold text-white tracking-tight mt-1">الإشعارات</h1>
        <p class="text-sm text-white/40 mt-1">كل ما يخص طلباتك وإيداعاتك في مكان واحد</p>
      </div>
      <button
        v-if="unreadCount > 0"
        @click="markAllAsRead"
        class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold bg-cyan-500/10 border border-cyan-400/20 text-cyan-400 rounded-xl hover:bg-cyan-500/20 transition-all active:scale-95"
      >
        <CheckCheck class="w-4 h-4" />
        تحديد الكل كمقروء
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <Loader2 class="w-8 h-8 text-cyan-400 animate-spin" />
    </div>

    <!-- Empty -->
    <div v-else-if="notifications.length === 0" class="flex flex-col items-center justify-center py-20 text-white/30">
      <div class="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mb-4">
        <Bell class="w-8 h-8 text-white/20" />
      </div>
      <p class="text-lg font-medium">لا توجد إشعارات</p>
      <p class="text-sm mt-1 text-white/40">عندما يصلك إشعار، سيظهر هنا</p>
    </div>

    <!-- List -->
    <div v-else class="space-y-2">
      <div
        v-for="notif in notifications"
        :key="notif.id"
        class="group relative flex items-start gap-4 p-5 rounded-2xl border transition-all duration-200 cursor-pointer"
        :class="notif.read_at ? 'bg-white/[0.02] border-white/5' : 'bg-cyan-500/[0.03] border-cyan-400/15'"
        @click="markAsRead(notif)"
      >
        <!-- Unread dot -->
        <div v-if="!notif.read_at" class="absolute top-5 right-5 w-2 h-2 rounded-full bg-cyan-400 shadow-[0_0_8px_rgba(34,211,238,0.4)]" />

        <!-- Icon -->
        <div class="shrink-0 w-11 h-11 rounded-xl flex items-center justify-center" :class="notif.read_at ? 'bg-white/5 border border-white/10' : 'bg-cyan-500/10 border border-cyan-400/20'">
          <component :is="notifIcon(notif.type)" class="w-5 h-5" :class="notif.read_at ? 'text-white/30' : 'text-cyan-400'" />
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0 pt-0.5">
          <p class="text-sm font-semibold" :class="notif.read_at ? 'text-white/60' : 'text-white'">
            {{ notifData(notif).title }}
          </p>
          <p v-if="notifData(notif).body" class="text-xs text-white/40 mt-1 leading-relaxed">
            {{ notifData(notif).body }}
          </p>
          <div class="flex items-center gap-3 mt-2">
            <span class="text-[10px] font-mono text-white/30">{{ timeAgo(notif.created_at) }}</span>
            <Link
              v-if="notifData(notif).url"
              :href="notifData(notif).url"
              class="text-[10px] font-mono text-cyan-400 hover:text-cyan-300 flex items-center gap-0.5 transition-colors"
              @click.stop
            >
              <ExternalLink class="w-3 h-3" /> فتح
            </Link>
          </div>
        </div>

        <!-- Chevron -->
        <ChevronLeft class="w-4 h-4 text-white/20 self-center flex-shrink-0" />
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && pagination.last_page > 1" class="flex items-center justify-center gap-2 pt-4">
      <button
        v-for="page in pagination.last_page"
        :key="page"
        @click="loadNotifications(page)"
        class="min-w-[40px] h-10 rounded-xl text-sm font-medium border transition-all"
        :class="page === pagination.current_page ? 'bg-cyan-500/10 border-cyan-400/20 text-cyan-400' : 'bg-white/[0.02] border-white/5 text-white/40 hover:text-white hover:border-white/20'"
      >
        {{ page }}
      </button>
    </div>
  </div>
</template>
