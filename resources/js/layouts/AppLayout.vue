<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/authStore';
import {
  Cpu, ShoppingBag, Wallet, Menu, X, ChevronLeft, ChevronRight,
  LogOut, Home,
} from 'lucide-vue-next';
import { Toaster } from '@/components/ui/sonner';
import PageTransition from '@/components/PageTransition.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';

const authStore = useAuthStore();
const mobileMenu = ref(false);
const scrolled = ref(false);

const onScroll = () => { scrolled.value = window.scrollY > 20; };
if (typeof window !== 'undefined') {
  window.addEventListener('scroll', onScroll, { passive: true });
}
</script>

<template>
  <Head>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />
  </Head>

  <div class="min-h-screen bg-[#050507] text-white relative isolate overflow-x-hidden font-sans">
    <!-- Ambient -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]" />
      <div class="absolute top-[-10%] left-1/2 -translate-x-1/2 w-[80%] h-[40%] bg-cyan-500/10 blur-[120px] rounded-full" />
      <div class="absolute bottom-[10%] right-[-5%] w-[40%] h-[40%] bg-purple-600/10 blur-[120px] rounded-full" />
    </div>

    <!-- Fixed Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-40 transition-all duration-300" :class="scrolled ? 'bg-[#070709]/80 backdrop-blur-xl border-b border-white/5' : 'bg-transparent'">
      <div class="w-full px-4 sm:px-6 lg:px-10 xl:px-16 h-20 flex items-center justify-between">
        <Link href="/" class="flex items-center gap-3 group relative z-10">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-cyan-500/10 border border-cyan-400/30 overflow-hidden">
            <Cpu class="w-5 h-5 text-cyan-400" />
          </div>
          <span class="text-xl font-bold tracking-wider text-white font-display">{{ $t('app.name') }}</span>
        </Link>

        <div class="hidden lg:flex items-center gap-8">
          <Link href="/" class="text-sm text-white font-medium relative flex items-center gap-1.5"><Home class="w-4 h-4" /> {{ $t('nav.home') }}<span class="absolute -bottom-2 right-0 w-full h-0.5 bg-cyan-400" /></Link>
          <Link href="/products" class="text-sm text-white/60 hover:text-white transition-colors relative group">{{ $t('nav.products') }}<span class="absolute -bottom-2 right-0 w-0 h-0.5 bg-cyan-400 group-hover:w-full transition-all duration-300" /></Link>
          <Link v-if="authStore.user" href="/orders" class="text-sm text-white/60 hover:text-white transition-colors relative group">{{ $t('nav.orders') }}<span class="absolute -bottom-2 right-0 w-0 h-0.5 bg-cyan-400 group-hover:w-full transition-all duration-300" /></Link>
          <Link v-if="authStore.user" href="/deposit" class="text-sm text-white/60 hover:text-white transition-colors relative group">{{ $t('nav.deposit') }}<span class="absolute -bottom-2 right-0 w-0 h-0.5 bg-cyan-400 group-hover:w-full transition-all duration-300" /></Link>
        </div>

        <div class="flex items-center gap-3">
          <LanguageSwitcher />
          <ThemeToggle />
          <Link v-if="authStore.user" href="/deposit" class="hidden sm:flex items-center gap-2 px-4 py-2 text-sm font-medium bg-white/5 text-white border border-white/10 rounded-xl hover:bg-cyan-500/10 hover:border-cyan-400/30 transition-all">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
            <span class="font-mono text-xs">$ {{ Number(authStore.user?.balance ?? 0).toFixed(2) }}</span>
          </Link>
          <Link v-if="!authStore.user" href="/login" class="hidden sm:block text-sm text-white/60 hover:text-white transition-colors px-4 py-2">{{ $t('nav.login') }}</Link>
          <Link v-if="!authStore.user" href="/register" class="hidden sm:block px-5 py-2 text-sm font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 transition-all hover:shadow-[0_0_25px_-5px_rgba(34,211,238,0.5)]">{{ $t('nav.startNow') }}</Link>
          <button class="lg:hidden w-10 h-10 flex items-center justify-center text-white hover:bg-white/5 rounded-lg transition-colors" @click="mobileMenu = !mobileMenu">
            <component :is="mobileMenu ? ChevronLeft : ChevronRight" class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div v-if="mobileMenu" class="lg:hidden bg-[#070709]/95 backdrop-blur-xl border-t border-white/5">
        <div class="px-6 py-4 flex flex-col gap-1">
          <Link href="/" class="text-sm text-white font-medium py-3 px-3 rounded-lg bg-cyan-500/5 text-cyan-400 flex items-center gap-2"><Home class="w-4 h-4" /> {{ $t('nav.home') }}</Link>
          <Link href="/products" class="text-sm text-white/60 hover:text-white py-3 px-3 rounded-lg hover:bg-white/5 transition-colors" @click="mobileMenu = false">{{ $t('nav.products') }}</Link>
          <Link v-if="authStore.user" href="/orders" class="text-sm text-white/60 hover:text-white py-3 px-3 rounded-lg hover:bg-white/5 transition-colors" @click="mobileMenu = false">{{ $t('nav.orders') }}</Link>
          <Link v-if="authStore.user" href="/deposit" class="text-sm text-white/60 hover:text-white py-3 px-3 rounded-lg hover:bg-white/5 transition-colors" @click="mobileMenu = false">{{ $t('nav.deposit') }}</Link>
          <div class="h-px bg-white/5 my-2" />
          <Link v-if="authStore.user" href="/deposit" class="flex items-center justify-between py-3 px-3 rounded-lg bg-white/5 text-white">
            <span class="text-sm">{{ $t('nav.balance') }}</span>
            <span class="font-mono text-sm text-cyan-400">$ {{ Number(authStore.user?.balance ?? 0).toFixed(2) }}</span>
          </Link>
          <Link v-if="!authStore.user" href="/login" class="text-center text-sm text-white/60 py-3 px-3 rounded-lg hover:bg-white/5 transition-colors">{{ $t('nav.login') }}</Link>
          <Link v-if="!authStore.user" href="/register" class="mt-2 px-6 py-3 text-sm font-semibold bg-cyan-400 text-black rounded-xl text-center">{{ $t('nav.startNow') }}</Link>
          <Link v-if="authStore.user" href="/logout" method="post" as="button" class="mt-2 text-center text-sm text-red-400 py-3 px-3 rounded-lg hover:bg-red-500/5 transition-colors flex items-center justify-center gap-2">
            <LogOut class="w-4 h-4" /> {{ $t('nav.logout') }}
          </Link>
        </div>
      </div>
    </nav>

    <!-- Content — CRITICAL: pt-20 pushes content below the fixed h-20 navbar -->
    <main class="relative z-10 pt-20">
      <PageTransition><slot /></PageTransition>
    </main>

    <Toaster />
  </div>
</template>