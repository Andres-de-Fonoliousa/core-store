<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import {
  Bell, CheckCheck, Package, Wallet, CreditCard, Info,
  ExternalLink, Loader2,
} from 'lucide-vue-next';

const api = useApi();
const toast = useToast();

const open = ref(false);
const loading = ref(false);
const notifications = ref<any[]>([]);
const unreadCount = ref(0);
let pollTimer: ReturnType<typeof setInterval> | null = null;

async function fetchNotifications() {
  loading.value = true;
  try {
    const { data } = await api.get('/notifications');
    notifications.value = data.data ?? [];
    unreadCount.value = data.meta?.unread ?? 0;
  } catch (error) {
    // silent fail on poll
  } finally {
    loading.value = false;
  }
}

async function fetchUnreadCount() {
  try {
    const { data } = await api.get('/notifications/unread-count');
    unreadCount.value = data.count ?? 0;
  } catch {
    // silent
  }
}

async function markAsRead(id: string) {
  try {
    await api.post(`/notifications/${id}/read`);
    const n = notifications.value.find(n => n.id === id);
    if (n) n.read_at = new Date().toISOString();
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  } catch (error) {
    toast.error(parseApiError(error));
  }
}

async function markAllAsRead() {
  try {
    await api.post('/notifications/read-all');
    notifications.value.forEach(n => n.read_at = new Date().toISOString());
    unreadCount.value = 0;
  } catch (error) {
    toast.error(parseApiError(error));
  }
}

function toggle() {
  open.value = !open.value;
  if (open.value) {
    fetchNotifications();
  }
}

function handleClickOutside(e: MouseEvent) {
  const target = e.target as HTMLElement;
  if (!target.closest('[data-notif-dropdown]')) {
    open.value = false;
  }
}

onMounted(() => {
  fetchUnreadCount();
  pollTimer = setInterval(fetchUnreadCount, 30000);
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer);
  document.removeEventListener('click', handleClickOutside);
});

function notifIcon(type: string) {
  if (type.includes('Order')) return Package;
  if (type.includes('Deposit')) return Wallet;
  if (type.includes('Transaction')) return CreditCard;
  return Info;
}

function notifData(notif: any) {
  const data = typeof notif.data === 'string' ? JSON.parse(notif.data) : notif.data;
  return {
    title: data?.title ?? data?.message ?? 'إشعار',
    body: data?.body ?? data?.message ?? '',
    url: data?.url ?? null,
  };
}

function formatTime(date: string) {
  const d = new Date(date);
  const now = new Date();
  const diff = now.getTime() - d.getTime();
  const minutes = Math.floor(diff / 60000);
  if (minutes < 1) return 'الآن';
  if (minutes < 60) return `منذ ${minutes} د`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `منذ ${hours} س`;
  const days = Math.floor(hours / 24);
  return `منذ ${days} ي`;
}
</script>

<template>
  <div data-notif-dropdown class="relative">
    <button @click.stop="toggle" class="relative w-10 h-10 flex items-center justify-center rounded-lg border border-white/10 text-white/40 hover:text-white hover:bg-white/5 transition-all">
      <Bell class="w-4 h-4" />
      <span v-if="unreadCount > 0" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center rounded-full bg-cyan-400 text-[10px] font-bold text-black px-1 leading-none">
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 scale-95 translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0" leave-active-class="transition-all duration-150 ease-in" leave-from-class="opacity-100 scale-100 translate-y-0" leave-to-class="opacity-0 scale-95 translate-y-2">
      <div v-if="open" class="absolute left-0 top-full mt-2 w-[360px] sm:w-[400px] max-h-[480px] bg-[#0c0c0e] border border-white/10 rounded-2xl shadow-2xl overflow-hidden z-50" dir="rtl">
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
          <h3 class="text-sm font-semibold text-white">الإشعارات</h3>
          <button v-if="unreadCount > 0" @click="markAllAsRead" class="flex items-center gap-1 text-[11px] font-mono text-cyan-400 hover:text-cyan-300 transition-colors">
            <CheckCheck class="w-3.5 h-3.5" /> تحديد الكل كمقروء
          </button>
        </div>

        <!-- List -->
        <div class="overflow-y-auto max-h-[380px]">
          <div v-if="loading && notifications.length === 0" class="flex items-center justify-center py-12">
            <Loader2 class="w-5 h-5 text-white/20 animate-spin" />
          </div>
          <div v-else-if="notifications.length === 0" class="flex flex-col items-center justify-center py-12 text-white/30">
            <Bell class="w-8 h-8 mb-3 text-white/20" />
            <p class="text-sm font-medium">لا توجد إشعارات</p>
          </div>
          <div v-for="notif in notifications" :key="notif.id" class="flex items-start gap-3 px-5 py-4 border-b border-white/5 hover:bg-white/[0.02] transition-colors cursor-pointer" :class="!notif.read_at ? 'bg-cyan-500/[0.02]' : ''" @click="markAsRead(notif.id)">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5" :class="!notif.read_at ? 'bg-cyan-500/10 border border-cyan-400/20' : 'bg-white/5 border border-white/10'">
              <component :is="notifIcon(notif.type)" class="w-4 h-4" :class="!notif.read_at ? 'text-cyan-400' : 'text-white/30'" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-white">{{ notifData(notif).title }}</p>
              <p v-if="notifData(notif).body" class="text-xs text-white/40 mt-0.5 line-clamp-2">{{ notifData(notif).body }}</p>
              <div class="flex items-center gap-2 mt-1.5">
                <span class="text-[10px] font-mono text-white/30">{{ formatTime(notif.created_at) }}</span>
                <span v-if="!notif.read_at" class="w-1.5 h-1.5 rounded-full bg-cyan-400" />
                <a v-if="notifData(notif).url" :href="notifData(notif).url" class="text-[10px] font-mono text-cyan-400 hover:text-cyan-300 flex items-center gap-0.5" @click.stop>
                  <ExternalLink class="w-3 h-3" /> فتح
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <Link href="/notifications" class="block text-center py-3 text-[11px] font-mono text-cyan-400 hover:text-cyan-300 border-t border-white/5 transition-colors">
          عرض الكل
        </Link>
      </div>
    </Transition>
  </div>
</template>
