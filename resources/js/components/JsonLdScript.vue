<script setup lang="ts">
defineProps<{ data: string }>()
</script>

<template>
  <component :is="'script'" type="application/ld+json" v-html="data" />
</template>
