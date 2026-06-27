<template>
  <div class="relative">
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-full border border-border bg-background px-3 py-2 text-sm text-foreground transition hover:border-primary hover:text-primary"
        >
          <span>{{ user?.name ?? 'Account' }}</span>
          <ChevronDown class="h-4 w-4" />
        </button>
      </DropdownMenuTrigger>
      <UserMenuContent :user="user" />
    </DropdownMenu>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/authStore';
import { ChevronDown } from '@lucide/vue';
import { DropdownMenu, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import type { User } from '@/types/auth';

const authStore = useAuthStore();
const user = authStore.user as User | null;
</script>
