<template>
  <select v-model="selected" @change="$emit('update:modelValue', selected)" class="rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground">
    <option :value="null">{{ $t('product.category') }}</option>
    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
  </select>
</template>

<script setup lang="ts">
import type { Category } from '@/types/models';
import { ref, watch } from 'vue';

const props = defineProps<{ categories: Category[]; modelValue: number | null }>();
const selected = ref(props.modelValue);

watch(() => props.modelValue, (value) => {
  selected.value = value;
});
</script>
