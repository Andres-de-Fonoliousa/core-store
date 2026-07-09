<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
  LayoutDashboard, Building2, LogOut, ArrowLeft,
} from 'lucide-vue-next';

const page = usePage();
const user = computed(() => (page.props as any).auth?.user ?? null);

const navigation = [
  { name: 'Dashboard', href: route('platform.admin.dashboard'), icon: LayoutDashboard },
  { name: 'Tenants', href: route('platform.admin.tenants'), icon: Building2 },
];
</script>

<template>
  <div class="min-h-screen bg-gray-950 text-gray-100">
    <header class="border-b border-gray-800 bg-gray-900/50 backdrop-blur-md sticky top-0 z-50">
      <div class="flex items-center justify-between px-6 py-3">
        <div class="flex items-center gap-6">
          <Link href="/" class="flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors">
            <ArrowLeft class="w-4 h-4" />
            Back to store
          </Link>
          <span class="text-sm font-semibold text-indigo-400">Platform Admin</span>
        </div>
        <div class="flex items-center gap-4">
          <nav class="hidden md:flex items-center gap-1">
            <Link
              v-for="item in navigation" :key="item.name"
              :href="item.href"
              class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-colors"
              :class="route().current(item.name) ? 'bg-gray-800 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'"
            >
              <component :is="item.icon" class="w-4 h-4" />
              {{ item.name }}
            </Link>
          </nav>
          <div v-if="user" class="flex items-center gap-2 text-sm text-gray-400">
            <span class="hidden sm:inline">{{ user.name }}</span>
            <Link href="/logout" method="post" as="button" class="flex items-center gap-1 text-red-400 hover:text-red-300 transition-colors">
              <LogOut class="w-4 h-4" />
            </Link>
          </div>
        </div>
      </div>
    </header>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </main>
  </div>
</template>
