import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useStorage } from '@vueuse/core';
import type { Product } from '@/types/models';

export interface CartItem {
  product: Product;
  quantity: number;
  params: Record<string, string>;
}

export const useCartStore = defineStore('cart', () => {
  const items = useStorage<CartItem[]>('cart-items', []);

  const count = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0));
  const total = computed(() => items.value.reduce((sum, item) => sum + item.product.price * item.quantity, 0));

  function addItem(product: Product, quantity: number, params: Record<string, string>) {
    const existing = items.value.find((item) => item.product.id === product.id);
    if (existing) {
      existing.quantity = quantity;
      existing.params = params;
    } else {
      items.value.push({ product, quantity, params });
    }
  }

  function removeItem(productId: number) {
    items.value = items.value.filter((item) => item.product.id !== productId);
  }

  function clear() {
    items.value = [];
  }

  return {
    items,
    count,
    total,
    addItem,
    removeItem,
    clear,
  };
});
