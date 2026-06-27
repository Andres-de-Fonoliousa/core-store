<script setup lang="ts">
import { type Component } from 'vue';

defineProps<{
  icon: Component;
  title: string;
  description?: string;
  actionLabel?: string;
  actionHref?: string;
}>();
</script>

<template>
  <div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="w-16 h-16 rounded-2xl bg-white/[0.02] border border-white/5 flex items-center justify-center mb-4">
      <component :is="icon" class="w-7 h-7 text-white/20" />
    </div>
    <h3 class="text-base font-semibold text-white mb-1">{{ title }}</h3>
    <p v-if="description" class="text-sm text-white/40 max-w-md mb-5">{{ description }}</p>
    <slot name="action" />
  </div>
</template>