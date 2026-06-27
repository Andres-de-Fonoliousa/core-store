<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/authStore';
import {
  LayoutDashboard, ShoppingBag, Wallet, CreditCard, Settings,
  LogOut, Menu, X, Cpu, ChevronLeft, Home, Bell,
} from 'lucide-vue-next';
import { Toaster } from '@/components/ui/sonner';
import PageTransition from '@/components/PageTransition.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import NotificationDropdown from '@/components/NotificationDropdown.vue';

const authStore = useAuthStore();
const page = usePage();
const mobileMenu = ref(false);

const navItems = [
  { label: 'الرئيسية', href: '/', icon: Home },
  { label: 'لوحة التحكم', href: '/dashboard', icon: LayoutDashboard },
  { label: 'طلباتي', href: '/orders', icon: ShoppingBag },
  { label: 'الإيداع', href: '/deposit', icon: CreditCard },
  { label: 'الإشعارات', href: '/notifications', icon: Bell },
  { label: 'الإعدادات', href: '/settings', icon: Settings },
];
</script>

<template>
  <Head>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />
  </Head>

  <div class="min-h-screen bg-[#050507] text-white relative isolate overflow-x-hidden" style="font-family: 'Space Grotesk', 'Cairo', sans-serif;">
    <!-- Ambient -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]" />
      <div class="absolute top-[-10%] left-1/2 -translate-x-1/2 w-[80%] h-[40%] bg-cyan-500/10 blur-[120px] rounded-full" />
    </div>

    <!-- Fixed Top Bar -->
    <header class="fixed top-0 left-0 right-0 h-20 z-40 border-b border-white/5 bg-[#070709]/90 backdrop-blur-xl">
      <div class="h-full px-6 lg:px-10 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <button @click="mobileMenu = !mobileMenu" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-lg border border-white/10 text-white/60 hover:text-white hover:bg-white/5 transition-all">
            <component :is="mobileMenu ? X : Menu" class="w-5 h-5" />
          </button>
          <Link href="/" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-cyan-500/10 border border-cyan-400/30">
              <Cpu class="w-5 h-5 text-cyan-400" />
            </div>
            <span class="font-bold tracking-wider text-white text-lg" style="font-family: 'Space Grotesk', sans-serif;">CoreS</span>
          </Link>
        </div>

        <nav class="hidden lg:flex items-center gap-1">
          <Link v-for="item in navItems" :key="item.href" :href="item.href" class="px-4 py-2 text-sm text-white/50 hover:text-white rounded-lg hover:bg-white/5 transition-all" :class="page.url.startsWith(item.href) ? 'text-cyan-400 bg-cyan-500/5' : ''">
            {{ item.label }}
          </Link>
        </nav>

        <div class="flex items-center gap-3">
          <LanguageSwitcher />
          <NotificationDropdown />
          <Link href="/deposit" class="hidden sm:flex items-center gap-2 px-4 py-2 text-sm font-medium bg-white/5 text-white border border-white/10 rounded-xl hover:bg-cyan-500/10 hover:border-cyan-400/30 transition-all">
            <Wallet class="w-4 h-4 text-cyan-400" />
            <span class="font-mono text-xs">$ {{ Number(authStore.user?.balance ?? 0).toFixed(2) }}</span>
          </Link>
          <div class="hidden md:flex items-center gap-3 px-3 py-2 rounded-xl bg-white/[0.02] border border-white/5">
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
          <Link v-for="item in navItems" :key="item.href" :href="item.href" class="flex items-center gap-3 text-sm text-white/60 hover:text-white py-3 px-3 rounded-lg hover:bg-white/5 transition-colors" :class="page.url.startsWith(item.href) ? 'text-cyan-400 bg-cyan-500/5' : ''" @click="mobileMenu = false">
            <component :is="item.icon" class="w-4 h-4" />
            {{ item.label }}
          </Link>
          <div class="h-px bg-white/5 my-2" />
          <Link href="/logout" method="post" as="button" class="flex items-center gap-2 text-sm text-red-400 py-3 px-3 rounded-lg hover:bg-red-500/5 transition-colors">
            <LogOut class="w-4 h-4" /> تسجيل الخروج
          </Link>
        </div>
      </div>
    </header>

    <!-- Content — CRITICAL: pt-20 pushes content below the fixed h-20 header -->
    <main class="relative z-10 pt-20 min-h-screen">
      <PageTransition>
        <div class="w-full px-4 sm:px-6 lg:px-10 py-8">
          <slot />
        </div>
      </PageTransition>
    </main>

    <Toaster />
  </div>
</template>