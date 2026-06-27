<template>
  <div class="flex items-center gap-2">
    <button type="button" @click="decrease" class="rounded-lg border border-border bg-background px-3 py-2 text-sm">-</button>
    <span class="min-w-[2rem] text-center text-base font-medium">{{ value }}</span>
    <button type="button" @click="increase" class="rounded-lg border border-border bg-background px-3 py-2 text-sm">+</button>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{ values: number[]; modelValue: number }>();
const emit = defineEmits(['update:modelValue']);
const value = ref(props.modelValue);

watch(() => props.modelValue, (next) => {
  value.value = next;
});

function increase() {
  const index = props.values.indexOf(value.value);
  if (index < props.values.length - 1) {
    value.value = props.values[index + 1];
    emit('update:modelValue', value.value);
  }
}

function decrease() {
  const index = props.values.indexOf(value.value);
  if (index > 0) {
    value.value = props.values[index - 1];
    emit('update:modelValue', value.value);
  }
}
</script>
