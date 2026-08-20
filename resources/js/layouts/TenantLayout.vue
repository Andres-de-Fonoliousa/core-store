<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useShopStore } from '@/stores/shopStore'

const shopStore = useShopStore()
const page = usePage()

const isAdminArea = computed(() => page.component.startsWith('Admin/'))
const isCustomerArea = computed(() => page.component.startsWith('Customer/'))
</script>

<template>
  <div
    class="tenant-wrapper min-h-screen bg-background text-foreground"
    :style="{
      '--brand': shopStore.brandColor,
      '--brand-dark': shopStore.brandColorDark,
    }"
  >
    <AdminLayout v-if="isAdminArea"><slot /></AdminLayout>
    <AuthenticatedLayout v-else-if="isCustomerArea"><slot /></AuthenticatedLayout>
    <slot v-else />
  </div>
</template>
