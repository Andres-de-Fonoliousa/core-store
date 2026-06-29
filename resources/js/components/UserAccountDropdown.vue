<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { onClickOutside } from '@vueuse/core';
import { useAuthStore } from '@/stores/authStore';
import {
  User, Settings, ShoppingBag, Wallet, LogOut, ChevronDown,
} from 'lucide-vue-next';

const authStore = useAuthStore();
const open = ref(false);
const menuRef = ref<HTMLElement | null>(null);
onClickOutside(menuRef, () => { open.value = false; });
</script>

<template>
  <div ref="menuRef" class="relative">
    <button
      @click="open = !open"
      class="flex items-center gap-2 px-3 py-2 text-sm font-medium bg-white/5 text-white border border-white/10 rounded-xl hover:bg-white/10 hover:border-white/20 transition-all"
    >
      <div class="w-7 h-7 rounded-full bg-gradient-to-br from-cyan-400/20 to-purple-400/20 border border-white/10 flex items-center justify-center">
        <span class="text-xs font-bold text-white">{{ authStore.user?.name?.charAt(0) ?? 'U' }}</span>
      </div>
      <span class="text-sm font-medium text-white/80 hidden sm:inline">{{ authStore.user?.name ?? 'User' }}</span>
      <ChevronDown class="w-3.5 h-3.5 text-white/40 transition-transform duration-200" :class="open ? 'rotate-180' : ''" />
    </button>

    <div
      v-if="open"
      class="absolute left-0 top-full mt-2 w-52 bg-[#0c0c0e] border border-white/10 rounded-xl shadow-2xl shadow-black/50 backdrop-blur-xl overflow-hidden z-50"
    >
      <div class="p-2 flex flex-col gap-0.5">
        <div class="px-3 py-2 border-b border-white/5 mb-1">
          <p class="text-sm font-medium text-white truncate">{{ authStore.user?.name ?? 'User' }}</p>
          <p class="text-[11px] font-mono text-white/40 truncate">{{ authStore.user?.email ?? '' }}</p>
          <div class="flex items-center gap-1.5 mt-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
            <span class="text-[10px] font-mono text-white/40">$ {{ Number(authStore.user?.balance ?? 0).toFixed(2) }}</span>
          </div>
        </div>
        <Link href="/profile" class="flex items-center gap-3 px-3 py-2.5 text-sm text-white/60 hover:text-white hover:bg-white/5 rounded-lg transition-colors" @click="open = false">
          <User class="w-4 h-4 text-cyan-400/70" /> {{ $t('nav.profile') }}
        </Link>
        <Link href="/orders" class="flex items-center gap-3 px-3 py-2.5 text-sm text-white/60 hover:text-white hover:bg-white/5 rounded-lg transition-colors" @click="open = false">
          <ShoppingBag class="w-4 h-4 text-cyan-400/70" /> {{ $t('nav.orders') }}
        </Link>
        <Link href="/deposit" class="flex items-center gap-3 px-3 py-2.5 text-sm text-white/60 hover:text-white hover:bg-white/5 rounded-lg transition-colors" @click="open = false">
          <Wallet class="w-4 h-4 text-cyan-400/70" /> {{ $t('nav.deposit') }}
        </Link>
        <Link href="/settings" class="flex items-center gap-3 px-3 py-2.5 text-sm text-white/60 hover:text-white hover:bg-white/5 rounded-lg transition-colors" @click="open = false">
          <Settings class="w-4 h-4 text-cyan-400/70" /> {{ $t('nav.settings') }}
        </Link>
        <div class="h-px bg-white/5 my-1" />
        <Link href="/logout" method="post" as="button" class="flex items-center gap-3 px-3 py-2.5 text-sm text-red-400/70 hover:text-red-400 hover:bg-red-500/5 rounded-lg transition-colors" @click="open = false">
          <LogOut class="w-4 h-4" /> {{ $t('nav.logout') }}
        </Link>
      </div>
    </div>
  </div>
</template>
