<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/authStore';
import {
  Cpu, Menu, X, ChevronDown, ChevronLeft, Home, LayoutDashboard,
  ShoppingBag, CreditCard, User, Settings, LogOut, Compass,
} from 'lucide-vue-next';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';

type NavAnchor = { label: string; href: string };

const props = withDefaults(defineProps<{
  /** Same-page section anchors (landing pages). Rendered before the real links. */
  anchors?: NavAnchor[];
}>(), {
  anchors: () => [],
});

const authStore = useAuthStore();
const page = usePage();

const scrolled = ref(false);
const mobileOpen = ref(false);
const userMenuOpen = ref(false);

const onScroll = () => { scrolled.value = window.scrollY > 12; };
onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

const isActive = (href: string) =>
  href !== '/' && page.url.startsWith(href);

const mainLinks = computed(() => {
  const links: { label: string; href: string; icon: any; auth?: boolean }[] = [
    { label: 'nav.home', href: '/', icon: Home },
    { label: 'nav.catalog', href: '/browse', icon: Compass },
    { label: 'nav.orders', href: '/orders', icon: ShoppingBag, auth: true },
    { label: 'nav.deposit', href: '/deposit', icon: CreditCard, auth: true },
    { label: 'nav.dashboard', href: '/dashboard', icon: LayoutDashboard, auth: true },
  ];
  return links;
});

const visibleLinks = computed(() =>
  mainLinks.value.filter((l) => !l.auth || authStore.user),
);

const userMenuItems = [
  { label: 'nav.dashboard', href: '/dashboard', icon: LayoutDashboard },
  { label: 'nav.profile', href: '/settings/profile', icon: User },
  { label: 'nav.settings', href: '/settings/appearance', icon: Settings },
];

const balance = computed(() =>
  Number((authStore.user as any)?.balance ?? 0).toFixed(2),
);
</script>

<template>
  <nav
    class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
    :class="scrolled ? 'glass-strong border-b border-primary/10' : 'bg-transparent'"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
      <!-- Logo -->
      <Link href="/" class="flex items-center gap-2.5 group shrink-0">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center border border-primary/30 bg-primary/10 glow-primary">
          <Cpu class="w-4 h-4 text-primary" />
        </div>
        <span class="font-display text-lg font-bold tracking-wider text-white group-hover:text-primary transition-colors">
          {{ $t('app.name') }}
        </span>
      </Link>

      <!-- Desktop nav -->
      <div class="hidden md:flex items-center gap-1 lg:gap-2">
        <template v-if="anchors.length">
          <a
            v-for="a in anchors" :key="a.href" :href="a.href"
            class="text-sm text-muted-foreground hover:text-primary transition-colors px-2 py-2"
          >{{ a.label }}</a>
        </template>
        <Link
          v-for="l in visibleLinks" :key="l.href" :href="l.href"
          class="text-sm px-2.5 py-2 rounded-md transition-all relative"
          :class="isActive(l.href)
            ? 'text-primary font-medium bg-primary/5'
            : 'text-muted-foreground hover:text-white'"
        >
          {{ $t(l.label) }}
        </Link>
      </div>

      <!-- Right side -->
      <div class="flex items-center gap-2 sm:gap-3">
        <div class="hidden sm:block"><LanguageSwitcher /></div>

        <!-- Balance chip (auth) -->
        <Link
          v-if="authStore.user"
          href="/deposit"
          class="hidden sm:flex items-center gap-2 px-3 py-1.5 text-xs font-mono bg-primary/5 border border-primary/20 text-primary rounded-md hover:bg-primary/10 transition-all"
        >
          ${{ balance }}
        </Link>

        <!-- Auth: user menu -->
        <div v-if="authStore.user" class="relative">
          <button
            @click="userMenuOpen = !userMenuOpen"
            class="flex items-center gap-2 px-2.5 py-1.5 text-sm font-medium bg-primary/5 text-primary border border-primary/20 rounded-md hover:bg-primary/10 transition-all"
          >
            <div class="w-6 h-6 rounded-full bg-primary/20 border border-primary/30 flex items-center justify-center">
              <span class="text-[10px] font-bold text-primary">{{ authStore.user?.name?.charAt(0) }}</span>
            </div>
            <span class="hidden lg:inline text-xs text-white/80">{{ authStore.user?.name }}</span>
            <ChevronDown class="w-3 h-3 text-primary/60 transition-transform duration-200" :class="userMenuOpen ? 'rotate-180' : ''" />
          </button>
          <div
            v-if="userMenuOpen"
            class="absolute left-0 top-full mt-2 w-52 bg-card border border-primary/10 rounded-lg shadow-2xl shadow-black/50 backdrop-blur-xl overflow-hidden z-50"
          >
            <div class="p-1.5 flex flex-col gap-0.5">
              <Link
                v-for="i in userMenuItems" :key="i.href" :href="i.href"
                class="flex items-center gap-3 px-3 py-2 text-sm text-muted-foreground hover:text-white hover:bg-primary/5 rounded-md transition-colors"
                @click="userMenuOpen = false"
              >
                <component :is="i.icon" class="w-4 h-4 text-primary/70" /> {{ $t(i.label) }}
              </Link>
              <div class="h-px bg-primary/10 my-1" />
              <Link
                href="/logout" method="post" as="button"
                class="flex items-center gap-3 px-3 py-2 text-sm text-red-400/70 hover:text-red-400 hover:bg-red-500/5 rounded-md transition-colors w-full text-right"
                @click="userMenuOpen = false"
              >
                <LogOut class="w-4 h-4" /> {{ $t('nav.logout') }}
              </Link>
            </div>
          </div>
        </div>

        <!-- Guest: auth CTAs -->
        <template v-else>
          <Link href="/login" class="hidden sm:inline text-sm text-muted-foreground hover:text-primary transition-colors magnetic">{{ $t('nav.login') }}</Link>
          <Link href="/register" class="magnetic hidden sm:inline px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-all glow-primary">
            {{ $t('nav.startNow') }}
          </Link>
        </template>

        <!-- Mobile toggle -->
        <button class="md:hidden w-10 h-10 flex items-center justify-center text-white" @click="mobileOpen = !mobileOpen" aria-label="Menu">
          <component :is="mobileOpen ? X : Menu" class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Mobile drawer -->
    <div v-if="mobileOpen" class="md:hidden glass-strong border-t border-primary/10">
      <div class="px-6 py-4 flex flex-col gap-1">
        <template v-if="anchors.length">
          <a v-for="a in anchors" :key="a.href" :href="a.href" class="text-sm text-muted-foreground hover:text-primary py-2.5 border-b border-primary/5" @click="mobileOpen = false">{{ a.label }}</a>
        </template>
        <Link
          v-for="l in visibleLinks" :key="l.href" :href="l.href"
          class="flex items-center gap-3 text-sm text-white/80 hover:text-primary py-2.5 px-1 rounded-md transition-colors"
          :class="isActive(l.href) ? 'text-primary' : ''"
          @click="mobileOpen = false"
        >
          <component :is="l.icon" class="w-4 h-4 text-primary/60" />
          {{ $t(l.label) }}
        </Link>
        <div class="py-2"><LanguageSwitcher /></div>
        <div class="h-px bg-primary/10 my-1" />

        <template v-if="authStore.user">
          <Link v-for="i in userMenuItems" :key="i.href" :href="i.href" class="flex items-center gap-3 text-sm text-muted-foreground hover:text-primary py-2.5 px-1" @click="mobileOpen = false">
            <component :is="i.icon" class="w-4 h-4 text-primary/70" /> {{ $t(i.label) }}
          </Link>
          <Link href="/logout" method="post" as="button" class="flex items-center gap-3 text-sm text-red-400/70 hover:text-red-400 py-2.5 px-1 text-right" @click="mobileOpen = false">
            <LogOut class="w-4 h-4" /> {{ $t('nav.logout') }}
          </Link>
        </template>
        <template v-else>
          <Link href="/login" class="text-sm text-muted-foreground hover:text-primary py-2.5 px-1" @click="mobileOpen = false">{{ $t('nav.login') }}</Link>
          <Link href="/register" class="px-5 py-2.5 text-sm font-medium bg-primary text-primary-foreground rounded-md text-center glow-primary" @click="mobileOpen = false">
            {{ $t('nav.register') }}
          </Link>
        </template>
      </div>
    </div>
  </nav>
</template>
