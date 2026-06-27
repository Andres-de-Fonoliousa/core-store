<template>
  <button
    type="button"
    @click="handleCopy"
    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
  >
    <Check v-if="copied" class="h-4 w-4" />
    <Copy v-else class="h-4 w-4" />
    {{ copied ? $t('order.copied') : $t('order.copyCode') }}
  </button>
</template>

<script setup lang="ts">
import { Copy, Check } from 'lucide-vue-next';
import { useClipboard } from '@/composables/useClipboard';

const props = defineProps<{ text: string }>();
const { copy, copied } = useClipboard();

async function handleCopy() {
  await copy(props.text);
}
</script>