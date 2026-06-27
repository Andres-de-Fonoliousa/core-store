<template>
  <div class="rounded-3xl border border-border bg-card p-6">
    <div class="mb-4">
      <h3 class="text-lg font-semibold">{{ title }}</h3>
      <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
    </div>
    <div class="aspect-[16/9] w-full">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string;
  description?: string;
}>();
</script>