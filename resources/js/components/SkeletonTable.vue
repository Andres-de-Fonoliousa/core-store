<script setup lang="ts">
defineProps<{ rows?: number; cols?: number }>();
</script>

<template>
  <div class="space-y-3">
    <div v-for="r in (rows ?? 5)" :key="r" class="flex items-center gap-4">
      <div v-for="c in (cols ?? 4)" :key="c" class="h-10 bg-white/5 rounded-lg animate-pulse flex-1" :class="c === 1 ? 'w-16 flex-shrink-0' : ''" />
    </div>
  </div>
</template>