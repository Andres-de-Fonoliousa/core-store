<template>
  <div class="group overflow-hidden rounded-xl border border-border bg-card shadow-sm transition hover:shadow-md">
    <div class="aspect-[4/3] overflow-hidden bg-muted">
      <img :src="product.image || '/placeholder.png'" :alt="product.name" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
    </div>
    <div class="p-4">
      <p class="text-xs text-muted-foreground">{{ product.category.name }}</p>
      <h3 class="mt-1 text-lg font-semibold leading-tight">{{ product.name }}</h3>
      <div class="mt-3 flex items-center justify-between gap-4">
        <span class="text-xl font-bold text-primary">{{ formatPrice(product.price) }}</span>
        <button @click="$emit('buy', product)" class="rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
          {{ $t('product.buyNow') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Product } from '@/types/models';

const props = defineProps<{ product: Product }>();

function formatPrice(value: number) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(value);
}
</script>
