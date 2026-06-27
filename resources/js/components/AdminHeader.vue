<template>
  <header class="flex items-center justify-between border-b bg-background px-6 py-4">
    <div>
      <h1 class="text-xl font-semibold text-foreground">{{ title }}</h1>
    </div>
  </header>
</template>

<script setup lang="ts">
const props = defineProps<{ title: string }>();
const title = props.title || '';
</script>
