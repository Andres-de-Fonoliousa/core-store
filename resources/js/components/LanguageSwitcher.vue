<template>
  <div class="inline-flex items-center gap-2 rounded-full border border-border bg-background px-3 py-2 text-sm text-foreground">
    <button
      type="button"
      @click="uiStore.setLocale('en')"
      :class="uiStore.locale === 'en' ? 'font-semibold text-primary' : 'text-muted-foreground'"
    >
      EN
    </button>
    <button
      type="button"
      @click="uiStore.setLocale('ar')"
      :class="uiStore.locale === 'ar' ? 'font-semibold text-primary' : 'text-muted-foreground'"
    >
      AR
    </button>
  </div>
</template>

<script setup lang="ts">
import { useUiStore } from '@/stores/uiStore';

const uiStore = useUiStore();
</script>
