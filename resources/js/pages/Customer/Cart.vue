<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useCartStore } from '@/stores/cartStore';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from '@/composables/useToast';
import {
  ShoppingCart, X, Plus, Minus, ArrowLeft, Trash2,
  ShoppingBag, AlertTriangle,
} from 'lucide-vue-next';

const cart = useCartStore();
const auth = useAuthStore();
const toast = useToast();

const subtotal = computed(() => cart.items.reduce((sum, item) => sum + (item.price * item.quantity), 0));

function updateQty(productId: number, delta: number) {
  const item = cart.items.find(i => i.id === productId);
  if (!item) return;
  const newQty = item.quantity + delta;
  if (newQty < 1) {
    cart.removeItem(productId);
  } else {
    cart.updateQuantity(productId, newQty);
  }
}

function removeItem(productId: number) {
  cart.removeItem(productId);
  toast.success('تمت إزالة المنتج');
}

function checkout() {
  if (!auth.user) {
    toast.warning('سجّل الدخول أولاً');
    return;
  }
  router.visit('/products');
}
</script>

<template>
  <Head title="سلة التسوق" />

  <div dir="rtl" class="min-h-screen bg-[#050507] text-white relative overflow-x-hidden" style="font-family: 'Cairo', 'Space Grotesk', sans-serif;">
    <!-- Ambient -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]" />
    </div>

    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 pt-10 pb-20">
      <!-- Header -->
      <div class="flex items-center gap-2 mb-8">
        <Link href="/products" class="text-xs text-white/40 hover:text-cyan-400 transition-colors flex items-center gap-1">
          <ArrowLeft class="w-3 h-3" /> المنتجات
        </Link>
        <span class="text-white/20">/</span>
        <span class="text-xs text-cyan-400">السلة</span>
      </div>

      <h1 class="text-3xl font-bold tracking-tight mb-2" style="font-family: 'Space Grotesk', sans-serif;">سلة التسوق</h1>
      <p class="text-sm text-white/40 mb-8">{{ cart.items.length }} منتج{{ cart.items.length !== 1 ? 'ات' : '' }}</p>

      <!-- Empty -->
      <div v-if="cart.items.length === 0" class="text-center py-20">
        <ShoppingCart class="w-12 h-12 text-white/20 mx-auto mb-4" />
        <h3 class="text-lg font-semibold text-white mb-2">السلة فارغة</h3>
        <p class="text-sm text-white/40 mb-6">أضف بعض المنتجات لتبدأ</p>
        <Link href="/products" class="inline-flex items-center gap-2 px-6 py-3 bg-cyan-400 text-black rounded-xl font-semibold hover:bg-cyan-300 transition-all">
          <ShoppingBag class="w-4 h-4" /> تصفح المنتجات
        </Link>
      </div>

      <div v-else class="grid lg:grid-cols-3 gap-6">
        <!-- Items -->
        <div class="lg:col-span-2 space-y-4">
          <div v-for="item in cart.items" :key="item.id" class="group bg-white/[0.02] border border-white/5 rounded-2xl p-4 flex items-center gap-4 hover:border-cyan-400/20 transition-all">
            <div class="w-20 h-20 rounded-xl bg-white/5 border border-white/10 overflow-hidden flex-shrink-0">
              <img :src="item.image || '/placeholder-product.png'" class="w-full h-full object-cover" />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-sm font-semibold text-white truncate group-hover:text-cyan-400 transition-colors">{{ item.name }}</h3>
              <p class="text-xs text-white/40 mt-1">{{ item.category }}</p>
              <p class="text-sm font-mono text-cyan-400 mt-2">${{ Number(item.price).toFixed(2) }}</p>
            </div>
            <div class="flex items-center gap-2">
              <button @click="updateQty(item.id, -1)" class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-white/60 hover:text-cyan-400 hover:border-cyan-400/30 transition-all">
                <Minus class="w-3.5 h-3.5" />
              </button>
              <span class="w-8 text-center font-mono text-sm">{{ item.quantity }}</span>
              <button @click="updateQty(item.id, 1)" class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-white/60 hover:text-cyan-400 hover:border-cyan-400/30 transition-all">
                <Plus class="w-3.5 h-3.5" />
              </button>
            </div>
            <div class="text-left">
              <p class="text-lg font-bold font-mono text-white">${{ Number(item.price * item.quantity).toFixed(2) }}</p>
            </div>
            <button @click="removeItem(item.id)" class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-white/30 hover:text-red-400 hover:border-red-400/30 hover:bg-red-400/10 transition-all">
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>

        <!-- Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl sticky top-24">
            <h3 class="text-sm font-semibold mb-4">ملخص الطلب</h3>
            <div class="space-y-3 mb-6">
              <div class="flex items-center justify-between text-sm">
                <span class="text-white/40">المجموع الفرعي</span>
                <span class="font-mono">${{ subtotal.toFixed(2) }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-white/40">الرسوم</span>
                <span class="font-mono text-emerald-400">$0.00</span>
              </div>
              <div class="h-px bg-white/5" />
              <div class="flex items-center justify-between">
                <span class="text-white font-semibold">الإجمالي</span>
                <span class="text-xl font-bold font-mono text-cyan-400">${{ subtotal.toFixed(2) }}</span>
              </div>
            </div>
            <button @click="checkout" class="w-full py-3.5 bg-cyan-400 text-black rounded-xl font-semibold hover:bg-cyan-300 transition-all active:scale-95 flex items-center justify-center gap-2 shadow-[0_0_30px_-5px_rgba(34,211,238,0.3)]">
              <ShoppingBag class="w-4 h-4" /> إتمام الشراء
            </button>
            <p class="text-[10px] text-white/30 text-center mt-3 font-mono">الدفع من رصيدك الحالي</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>