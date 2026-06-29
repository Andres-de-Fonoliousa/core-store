<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  User, Lock, Palette, ChevronLeft,
} from 'lucide-vue-next';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';

const page = usePage();
const currentUrl = computed(() => page.url);

const { t } = useI18n();

const sidebarNavItems = [
  { title: t('settings.profile'), href: editProfile(), icon: User },
  { title: t('settings.security'), href: editSecurity(), icon: Lock },
  { title: t('settings.appearance'), href: editAppearance(), icon: Palette },
];

function isCurrent(href: string) {
  return currentUrl.value === href || currentUrl.value.startsWith(href + '/');
}
</script>

<template>
  <div class="space-y-8">
    <!-- Header -->
    <div>
      <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">{{ $t('settings.title') }}</span>
      <h1 class="text-3xl font-bold tracking-tight mt-1 text-white">{{ $t('settings.heading') }}</h1>
      <p class="text-sm text-white/40 mt-1">{{ $t('settings.description') }}</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
      <!-- Sidebar -->
      <aside class="w-full lg:w-56 flex-shrink-0">
        <nav class="flex flex-row lg:flex-col gap-2 overflow-x-auto lg:overflow-visible pb-2 lg:pb-0 no-scrollbar">
          <Link v-for="item in sidebarNavItems" :key="item.href" :href="item.href" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 whitespace-nowrap" :class="isCurrent(item.href) ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-400/20' : 'text-white/50 hover:text-white hover:bg-white/5 border border-transparent'">
            <component :is="item.icon" class="w-4 h-4" />
            {{ item.title }}
            <ChevronLeft v-if="isCurrent(item.href)" class="w-3 h-3 mr-auto text-cyan-400/50 hidden lg:block" />
          </Link>
        </nav>
      </aside>

      <!-- Divider -->
      <div class="h-px lg:h-auto lg:w-px bg-white/5" />

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <div class="max-w-2xl space-y-8">
          <slot />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>