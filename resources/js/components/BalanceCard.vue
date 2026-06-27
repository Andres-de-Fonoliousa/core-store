<template>
  <div class="rounded-2xl border border-border bg-card p-3 text-sm text-foreground shadow-sm">
    <div class="flex items-center justify-between gap-4">
      <div>
        <p class="text-xs uppercase tracking-[0.2em] text-muted-foreground">Balance</p>
        <p class="text-lg font-semibold">{{ formattedBalance }}</p>
      </div>
      <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">Available</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useBalance } from '@/composables/useBalance';

const { formattedBalance } = useBalance();
</script>
