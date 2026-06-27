<template>
  <button
    type="button"
    @click="toggleTheme"
    class="inline-flex items-center justify-center rounded-full border border-border bg-background p-2 text-sm text-foreground transition hover:border-primary hover:text-primary"
    :aria-label="`Switch theme to ${nextTheme}`"
  >
    <span v-if="theme === 'dark'">☾</span>
    <span v-else-if="theme === 'light'">☀</span>
    <span v-else>⚙</span>
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useTheme, type Theme } from '@/composables/useTheme';

const { theme, setTheme } = useTheme();

const nextTheme = computed(() => {
  if (theme.value === 'light') return 'dark';
  if (theme.value === 'dark') return 'system';
  return 'light';
});

function toggleTheme() {
  setTheme(nextTheme.value as Theme);
}
</script>
