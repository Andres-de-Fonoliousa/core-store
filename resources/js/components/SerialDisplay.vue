<template>
  <div class="rounded-xl border border-border bg-card p-6 text-center">
    <div v-if="fulfilled && code" class="space-y-4">
      <div class="text-sm text-muted-foreground">{{ $t('order.serialCode') }}</div>
      <div class="rounded-lg bg-muted p-4 text-2xl font-mono tracking-wide">{{ code }}</div>
      <button @click="copyCode" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground">
        {{ copied ? $t('order.copied') : $t('order.copyCode') }}
      </button>
    </div>
    <div v-else-if="!fulfilled" class="space-y-3">
      <div class="h-10 rounded-lg bg-muted animate-pulse"></div>
      <p class="text-muted-foreground">{{ $t('order.status.pending_fulfillment') }}</p>
    </div>
    <div v-else class="space-y-3 text-destructive">
      <div class="text-xl font-semibold">{{ $t('order.status.failed') }}</div>
      <p class="text-sm text-muted-foreground">Contact support for assistance.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useClipboard } from '@/composables/useClipboard';

const props = defineProps<{ code: string | null; fulfilled: boolean }>();
const { copy, copied } = useClipboard();

async function copyCode() {
  if (!props.code) return;
  await copy(props.code);
}
</script>
