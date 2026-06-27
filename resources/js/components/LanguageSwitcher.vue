<template>
  <div class="inline-flex items-center gap-2 rounded-full border border-border bg-background px-3 py-2 text-sm text-foreground">
    <button
      type="button"
      @click="setLocale('en')"
      :class="selectedLocale === 'en' ? 'font-semibold text-primary' : 'text-muted-foreground'"
    >
      EN
    </button>
    <button
      type="button"
      @click="setLocale('ar')"
      :class="selectedLocale === 'ar' ? 'font-semibold text-primary' : 'text-muted-foreground'"
    >
      AR
    </button>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { i18n, setLocale } from '@/i18n';

const selectedLocale = computed(() => i18n.global.locale.value as 'en' | 'ar');
</script>
