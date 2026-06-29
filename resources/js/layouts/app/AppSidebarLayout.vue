<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, usePage } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/authStore';
import {
  LayoutDashboard, Box, Tag, Server, CreditCard,
  ArrowLeftRight, Settings, LogOut, Menu, X, Cpu, Zap,
  ChevronRight,
} from 'lucide-vue-next';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = { breadcrumbs?: BreadcrumbItem[] };
withDefaults(defineProps<Props>(), { breadcrumbs: () => [] });

const authStore = useAuthStore();
const page = usePage();
const sidebarOpen = ref(false);
const currentRoute = computed(() => page.url);

const { t } = useI18n();

const navItems = [
  { label: t('admin.dashboard'), href: '/admin/dashboard', icon: LayoutDashboard },
  { label: t('admin.products'), href: '/admin/products', icon: Box },
  { label: t('admin.categories'), href: '/admin/categories', icon: Tag },
  { label: t('admin.providers'), href: '/admin/providers', icon: Server },
  { label: t('admin.deposits'), href: '/admin/deposits', icon: CreditCard },
  { label: t('admin.transactions'), href: '/admin/transactions', icon: ArrowLeftRight },
  { label: t('admin.settings'), href: '/admin/settings', icon: Settings },
];

function isActive(href: string) {
  return currentRoute.value.startsWith(href);
}
</script>

<template>
  <Head>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />
  </Head>

  <div class="min-h-screen bg-[#050507] text-white relative overflow-x-hidden font-sans">
    <!-- Ambient -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]" />
      <div class="absolute top-[-10%] left-1/2 -translate-x-1/2 w-[80%] h-[40%] bg-cyan-500/10 blur-[120px] rounded-full" />
      <div class="absolute bottom-[10%] right-[-5%] w-[40%] h-[40%] bg-purple-600/10 blur-[120px] rounded-full" />
    </div>

    <!-- Mobile Overlay -->
    <div v-if="sidebarOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden" @click="sidebarOpen = false" />

    <!-- Sidebar -->
    <aside class="fixed top-0 right-0 h-full w-64 z-50 transform transition-transform duration-300 lg:translate-x-0 lg:right-auto lg:left-0 lg:z-30 border-r border-white/5 bg-[#070709]/90 backdrop-blur-2xl" :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'">
      <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="h-20 flex items-center gap-3 px-6 border-b border-white/5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-cyan-500/10 border border-cyan-400/30">
            <Cpu class="w-5 h-5 text-cyan-400" />
          </div>
          <div>
            <span class="font-bold tracking-wider text-white text-lg font-display">{{ $t('app.name') }}</span>
            <p class="text-[10px] font-mono text-white/40 tracking-wider">{{ $t('admin.panel') }}</p>
          </div>
          <button @click="sidebarOpen = false" class="lg:hidden ml-auto text-white/40 hover:text-white">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
          <Link v-for="item in navItems" :key="item.href" :href="item.href" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 group" :class="isActive(item.href) ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-400/20' : 'text-white/50 hover:text-white hover:bg-white/5 border border-transparent'" @click="sidebarOpen = false">
            <component :is="item.icon" class="w-[18px] h-[18px] transition-colors" :class="isActive(item.href) ? 'text-cyan-400' : 'text-white/30 group-hover:text-white/70'" />
            {{ item.label }}
            <Zap v-if="isActive(item.href)" class="w-3 h-3 mr-auto text-cyan-400/50" />
          </Link>
        </nav>

        <!-- User -->
        <div class="p-4 border-t border-white/5">
          <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/[0.02] border border-white/5">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-400/20 to-purple-400/20 border border-white/10 flex items-center justify-center">
              <span class="text-xs font-bold text-white">{{ authStore.user?.name?.charAt(0) ?? 'A' }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-white truncate">{{ authStore.user?.name ?? 'Admin' }}</p>
              <p class="text-[10px] font-mono text-white/40 truncate">{{ authStore.user?.email ?? 'astroid198@gmail.com' }}</p>
            </div>
            <Link href="/logout" method="post" as="button" class="text-white/20 hover:text-red-400 transition-colors">
              <LogOut class="w-4 h-4" />
            </Link>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main -->
    <div class="lg:ml-64 min-h-screen flex flex-col">
      <!-- Top Bar -->
      <header class="h-20 flex items-center justify-between px-6 lg:px-10 border-b border-white/5 sticky top-0 z-20 bg-[#050507]/80 backdrop-blur-xl">
        <div class="flex items-center gap-4">
          <button @click="sidebarOpen = true" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-lg border border-white/10 text-white/60 hover:text-white hover:bg-white/5 transition-all">
            <Menu class="w-5 h-5" />
          </button>
          <div>
            <h1 class="text-lg font-semibold text-white tracking-tight">{{ $page.props.title ?? $t('admin.dashboard') }}</h1>
            <div class="hidden sm:flex items-center gap-2 text-[11px] font-mono text-white/40">
              <Link v-for="(crumb, i) in breadcrumbs" :key="crumb.href" :href="crumb.href" class="hover:text-cyan-400 transition-colors">
                {{ crumb.title }} <span v-if="i < breadcrumbs.length - 1" class="mx-1 text-white/20">/</span>
              </Link>
              <span v-if="breadcrumbs.length === 0">{{ $t('admin.dashboard') }}</span>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/5 border border-emerald-400/20">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" />
            <span class="text-[10px] font-mono text-emerald-400 tracking-wider">{{ $t('admin.systemOnline') }}</span>
          </div>
        </div>
      </header>

      <main class="flex-1 w-full px-4 sm:px-6 lg:px-10 py-8 relative z-10">
        <slot />
      </main>
    </div>

    <Toaster />
  </div>
</template>