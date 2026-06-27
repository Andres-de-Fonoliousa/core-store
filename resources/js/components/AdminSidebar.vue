<template>
  <aside :class="['fixed inset-y-0 left-0 z-50 bg-sidebar border-r transition-all duration-300', collapsed ? 'w-16' : 'w-64']">
    <div class="flex h-14 items-center border-b px-4">
      <span v-if="!collapsed" class="font-bold text-sidebar-foreground">Admin</span>
      <button @click="$emit('toggle')" class="ml-auto p-2 text-sidebar-foreground">
        <PanelLeft class="w-5 h-5" />
      </button>
    </div>

    <nav class="space-y-1 p-2">
      <Link
        v-for="item in menuItems"
        :key="item.href"
        :href="item.href"
        class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors"
        :class="isActive(item.href) ? 'bg-sidebar-primary text-sidebar-primary-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'"
      >
        <component :is="item.icon" class="w-5 h-5" />
        <span v-if="!collapsed">{{ item.label }}</span>
      </Link>
    </nav>
  </aside>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { PanelLeft, LayoutDashboard, Wallet, Tags, Truck, Package, Settings, ArrowLeftRight } from 'lucide-vue-next';

const props = defineProps<{ collapsed: boolean }>();
const emit = defineEmits(['toggle']);
const page = usePage();

const menuItems = [
  { href: '/admin', label: 'Dashboard', icon: LayoutDashboard },
  { href: '/admin/deposits', label: 'Deposits', icon: Wallet },
  { href: '/admin/transactions', label: 'Transactions', icon: ArrowLeftRight },
  { href: '/admin/categories', label: 'Categories', icon: Tags },
  { href: '/admin/providers', label: 'Providers', icon: Truck },
  { href: '/admin/products', label: 'Products', icon: Package },
  { href: '/admin/settings', label: 'Settings', icon: Settings },
];

function isActive(href: string) {
  return page.url.startsWith(href);
}
</script>
