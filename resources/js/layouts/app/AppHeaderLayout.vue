<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/authStore';
import {
  Menu, X, Cpu, Zap, LogOut, Bell,
} from 'lucide-vue-next';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = { breadcrumbs?: BreadcrumbItem[] };
withDefaults(defineProps<Props>(), { breadcrumbs: () => [] });

const authStore = useAuthStore();
const page = usePage();
const mobileMenu = ref(false);
const currentRoute = computed(() => page.url);
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
    </div>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-40 h-20 border-b border-white/5 bg-[#050507]/80 backdrop-blur-xl">
      <div class="h-full px-6 lg:px-10 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <button @click="mobileMenu = !mobileMenu" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-lg border border-white/10 text-white/60 hover:text-white hover:bg-white/5 transition-all">
            <component :is="mobileMenu ? X : Menu" class="w-5 h-5" />
          </button>
          <Link href="/" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-cyan-500/10 border border-cyan-400/30">
              <Cpu class="w-5 h-5 text-cyan-400" />
            </div>
            <span class="font-bold tracking-wider text-white text-lg font-display">{{ $t('app.name') }}</span>
          </Link>
        </div>

        <!-- Desktop Nav -->
        <nav class="hidden lg:flex items-center gap-1">
          <Link v-for="crumb in breadcrumbs" :key="crumb.href" :href="crumb.href" class="px-3 py-1.5 text-sm text-white/50 hover:text-white transition-colors rounded-lg hover:bg-white/5">
            {{ crumb.title }}
          </Link>
        </nav>

        <!-- Right -->
        <div class="flex items-center gap-3">
          <button class="relative w-10 h-10 flex items-center justify-center rounded-lg border border-white/10 text-white/40 hover:text-white hover:bg-white/5 transition-all">
            <Bell class="w-4 h-4" />
            <span class="absolute top-2 right-2 w-1.5 h-1.5 rounded-full bg-cyan-400" />
          </button>
          <div class="hidden sm:flex items-center gap-3 px-3 py-2 rounded-xl bg-white/[0.02] border border-white/5">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-cyan-400/20 to-purple-400/20 border border-white/10 flex items-center justify-center">
              <span class="text-xs font-bold text-white">{{ authStore.user?.name?.charAt(0) ?? 'A' }}</span>
            </div>
            <span class="text-sm font-medium text-white/80">{{ authStore.user?.name ?? 'User' }}</span>
          </div>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div v-if="mobileMenu" class="lg:hidden border-t border-white/5 bg-[#070709]/95 backdrop-blur-xl">
        <div class="px-6 py-4 flex flex-col gap-1">
          <Link v-for="crumb in breadcrumbs" :key="crumb.href" :href="crumb.href" class="text-sm text-white/60 hover:text-white py-3 px-3 rounded-lg hover:bg-white/5 transition-colors" @click="mobileMenu = false">
            {{ crumb.title }}
          </Link>
          <div class="h-px bg-white/5 my-2" />
          <Link href="/logout" method="post" as="button" class="flex items-center gap-2 text-sm text-red-400 py-3 px-3 rounded-lg hover:bg-red-500/5 transition-colors">
            <LogOut class="w-4 h-4" /> {{ $t('nav.logout') }}
          </Link>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="pt-20 min-h-screen relative z-10">
      <div class="w-full px-4 sm:px-6 lg:px-10 py-8">
        <slot />
      </div>
    </main>

    <Toaster />
  </div>
</template>