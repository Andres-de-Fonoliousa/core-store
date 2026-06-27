<template>
  <div class="rounded-3xl border border-border bg-card p-6">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-sm text-muted-foreground">{{ label }}</p>
        <p class="mt-3 text-3xl font-semibold">{{ value }}</p>
      </div>
      <div v-if="icon" class="rounded-2xl bg-primary/10 p-3 text-primary">
        <component :is="icon" class="h-5 w-5" />
      </div>
    </div>
    <div v-if="change !== undefined" class="mt-4 flex items-center gap-1 text-sm">
      <TrendingUp v-if="change > 0" class="h-4 w-4 text-success" />
      <TrendingDown v-else class="h-4 w-4 text-destructive" />
      <span :class="change > 0 ? 'text-success' : 'text-destructive'">
        {{ change > 0 ? '+' : '' }}{{ change }}%
      </span>
      <span class="text-muted-foreground">vs last month</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Component } from 'vue';
import { TrendingUp, TrendingDown } from 'lucide-vue-next';

defineProps<{
  label: string;
  value: string | number;
  icon?: Component;
  change?: number;
}>();
</script>