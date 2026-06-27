<template>
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    <ProductCard v-for="product in products" :key="product.id" :product="product" @buy="$emit('buy', $event)" />
  </div>
</template>

<script setup lang="ts">
import ProductCard from './ProductCard.vue';
import type { Product } from '@/types/models';

const props = defineProps<{ products: Product[] }>();
</script>
