<template>
  <div class="space-y-3 rounded-3xl border border-border bg-background p-4">
    <div class="grid gap-2">
      <label class="text-sm font-medium text-foreground">Proof document</label>
      <input type="file" accept="image/*,application/pdf" @change="onFileChange" class="w-full rounded-2xl border border-border bg-card px-4 py-3 text-sm text-foreground" />
    </div>

    <div v-if="preview" class="space-y-2">
      <p class="text-xs font-medium uppercase tracking-[0.18em] text-muted-foreground">Preview</p>
      <img v-if="isImage" :src="preview" alt="Proof preview" class="w-full rounded-2xl border border-border object-contain" />
      <p v-else class="text-sm text-muted-foreground">File ready for upload.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onBeforeUnmount } from 'vue';

const props = defineProps<{ modelValue: File | null }>();
const emit = defineEmits(['update:modelValue', 'preview']);
const preview = ref('');
const isImage = ref(false);
let objectUrl = '';

watch(
  () => props.modelValue,
  (file) => {
    if (objectUrl) {
      URL.revokeObjectURL(objectUrl);
      objectUrl = '';
      preview.value = '';
      isImage.value = false;
    }

    if (file instanceof File) {
      isImage.value = file.type.startsWith('image/');
      objectUrl = URL.createObjectURL(file);
      preview.value = objectUrl;
      emit('preview', objectUrl);
    }
  },
  { immediate: true },
);

onBeforeUnmount(() => {
  if (objectUrl) {
    URL.revokeObjectURL(objectUrl);
  }
});

function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0] ?? null;
  emit('update:modelValue', file);
}
</script>
