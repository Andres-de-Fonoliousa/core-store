<template>
  <select v-model="selected" @change="$emit('update:modelValue', selected)" class="rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground">
    <option :value="null">{{ $t('product.provider') }}</option>
    <option v-for="provider in providers" :key="provider.id" :value="provider.id">{{ provider.name }}</option>
  </select>
</template>

<script setup lang="ts">
import type { Provider } from '@/types/models';
import { ref, watch } from 'vue';

const props = defineProps<{ providers: Provider[]; modelValue: number | null }>();
const selected = ref(props.modelValue);

watch(() => props.modelValue, (value) => {
  selected.value = value;
});
</script>
