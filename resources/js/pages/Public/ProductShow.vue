<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import { safeJsonLd, buildProductJsonLd, buildBreadcrumbJsonLd, buildStoreJsonLd, defaultDescription, siteName } from '@/lib/seo';
import JsonLdScript from '@/components/JsonLdScript.vue';
import {
  ShoppingCart, Zap, ChevronLeft, Star, ShieldCheck,
  Clock, Package, AlertTriangle, CheckCircle, Cpu,
  ArrowLeft, Minus, Plus, User, CreditCard,
} from 'lucide-vue-next';

const props = defineProps<{ product: any }>();
const api = useApi();
const auth = useAuthStore();
const toast = useToast();

const quantity = ref(1);
const orderLoading = ref(false);
const formValues = ref<Record<string, string>>({});
const showSuccess = ref(false);
const orderResult = ref<any>(null);

const user = computed(() => auth.user);
const isAuto = computed(() => props.product?.is_auto);
const params = computed(() => props.product?.params ?? []);
const qtyValues = computed(() => props.product?.qty_values ?? []);

function setQty(v: number) { quantity.value = v; }
function incQty() { if (qtyValues.value.length) { const idx = qtyValues.value.indexOf(quantity.value); if (idx < qtyValues.value.length - 1) quantity.value = qtyValues.value[idx + 1]; } else { quantity.value++; } }
function decQty() { if (qtyValues.value.length) { const idx = qtyValues.value.indexOf(quantity.value); if (idx > 0) quantity.value = qtyValues.value[idx - 1]; } else { if (quantity.value > 1) quantity.value--; } }

function fmtMoney(v: any) {
  const n = typeof v === 'string' ? parseFloat(v) : Number(v);
  return isNaN(n) ? '0.00' : n.toFixed(2);
}

const totalPrice = computed(() => {
  const base = typeof props.product?.price === 'string' ? parseFloat(props.product.price) : Number(props.product?.price ?? 0);
  return (base * quantity.value).toFixed(2);
});

async function placeOrder() {
  if (!user.value) {
    toast.warning('سجّل الدخول أولاً لإتمام الشراء');
    setTimeout(() => window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname), 800);
    return;
  }
  const missing = params.value.filter((p: string) => !formValues.value[p]?.trim());
  if (missing.length) {
    toast.error('أكمل الحقول المطلوبة: ' + missing.join(', '));
    return;
  }
  orderLoading.value = true;
  try {
    const payload: any = { product_id: props.product.id, quantity: quantity.value };
    if (params.value.length) {
      payload.playerId = formValues.value[params.value[0]] ?? '';
      payload.params = formValues.value;
    }
    const { data } = await api.post('/orders', payload);
    orderResult.value = data;
    showSuccess.value = true;
    toast.success('تم إنشاء الطلب بنجاح');
  } catch (err: any) {
    toast.error(parseApiError(err));
  } finally {
    orderLoading.value = false;
  }
}

onMounted(() => {
  if (qtyValues.value.length) quantity.value = qtyValues.value[0];
  params.value.forEach((p: string) => { if (!formValues.value[p]) formValues.value[p] = ''; });
});

const seoTitle = computed(() => props.product ? `${props.product.name} | ${siteName}` : siteName);
const seoDescription = computed(() => props.product?.description ?? defaultDescription);
const seoImage = computed(() => {
  if (!props.product?.image) return undefined;
  const base = window.location.origin;
  return props.product.image.startsWith('http') ? props.product.image : `${base}/storage/${props.product.image}`;
});
const canonicalUrl = computed(() => props.product ? `${window.location.origin}/products/${props.product.id}` : window.location.href);
const productJsonLd = computed(() => props.product ? safeJsonLd(buildProductJsonLd(props.product)) : '');
const breadcrumbJsonLd = computed(() => {
  const items = [{ name: 'الرئيسية', url: window.location.origin }];
  if (props.product?.categoryName) {
    items.push({ name: props.product.categoryName, url: `${window.location.origin}/browse/${props.product.category_id}` });
  }
  items.push({ name: props.product?.name ?? 'المنتج', url: canonicalUrl.value });
  return safeJsonLd(buildBreadcrumbJsonLd(items));
});
const storeJsonLd = computed(() => safeJsonLd(buildStoreJsonLd()));
</script>

<template>
  <Head :title="product?.name ?? 'المنتج'">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <meta name="description" :content="seoDescription" />
    <link rel="canonical" :href="canonicalUrl" />

    <meta property="og:type" content="product" />
    <meta property="og:site_name" :content="$t('app.name')" />
    <meta property="og:title" :content="seoTitle" />
    <meta property="og:description" :content="seoDescription" />
    <meta property="og:url" :content="canonicalUrl" />
    <meta v-if="seoImage" property="og:image" :content="seoImage" />
    <meta property="og:locale" content="ar_SY" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" :content="seoTitle" />
    <meta name="twitter:description" :content="seoDescription" />
    <meta v-if="seoImage" name="twitter:image" :content="seoImage" />

    <JsonLdScript v-if="productJsonLd" :data="productJsonLd" />
    <JsonLdScript :data="breadcrumbJsonLd" />
    <JsonLdScript :data="storeJsonLd" />
  </Head>

  <div class="min-h-screen bg-[#050507] text-white relative overflow-x-hidden font-sans">
    <!-- Ambient -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]" />
      <div class="absolute top-[-10%] left-1/2 -translate-x-1/2 w-[60%] h-[40%] bg-cyan-500/10 blur-[120px] rounded-full" />
    </div>

    <!-- Navbar (Compact) -->
    <nav class="fixed top-0 left-0 right-0 z-40 bg-[#070709]/80 backdrop-blur-xl border-b border-white/5">
      <div class="w-full px-4 sm:px-6 lg:px-10 xl:px-16 h-16 flex items-center justify-between">
        <Link href="/" class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-cyan-500/10 border border-cyan-400/30">
            <Cpu class="w-4 h-4 text-cyan-400" />
          </div>
          <span class="text-lg font-bold tracking-wider text-white font-display">{{ $t('app.name') }}</span>
        </Link>
        <Link href="/products" class="text-sm text-white/60 hover:text-white transition-colors flex items-center gap-1">
          <ArrowLeft class="w-4 h-4" /> {{ $t('common.back') }}
        </Link>
      </div>
    </nav>

    <main class="relative z-10 pt-24 pb-20 px-4 sm:px-6 lg:px-10 xl:px-16">
      <!-- Breadcrumb -->
      <div class="flex items-center gap-2 text-xs text-white/30 font-mono mb-6">
        <Link href="/" class="hover:text-cyan-400 transition-colors">{{ $t('nav.home') }}</Link>
        <span>/</span>
        <Link href="/products" class="hover:text-cyan-400 transition-colors">{{ $t('nav.products') }}</Link>
        <span>/</span>
        <span class="text-white/60 truncate max-w-[200px]">{{ product?.name }}</span>
      </div>

      <div v-if="showSuccess && orderResult" class="mb-8 bg-emerald-500/5 border border-emerald-400/20 rounded-2xl p-6 backdrop-blur-xl">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center flex-shrink-0">
            <CheckCircle class="w-6 h-6 text-emerald-400" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-white mb-1">{{ $t('product.orderSuccess') }}</h3>
            <p class="text-sm text-white/60 mb-3">{{ $t('product.orderNumber') }}: <span class="font-mono text-emerald-400">#{{ orderResult.id }}</span></p>
            <div class="flex gap-3">
              <Link :href="`/orders/${orderResult.id}`" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold bg-emerald-400/10 text-emerald-400 border border-emerald-400/20 rounded-lg hover:bg-emerald-400/20 transition-all">
                <Package class="w-3.5 h-3.5" /> {{ $t('product.orderDetails') }}
              </Link>
              <Link href="/orders" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold bg-white/5 text-white border border-white/10 rounded-lg hover:bg-white/10 transition-all">
                <ShoppingCart class="w-3.5 h-3.5" /> {{ $t('product.myOrders') }}
              </Link>
            </div>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-12 gap-8">
        <!-- Product Image -->
        <div class="lg:col-span-5">
          <div class="relative bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden group">
            <div class="aspect-square overflow-hidden relative">
              <img :src="product?.image || '/placeholder-product.png'" :alt="product?.name" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
              <div class="absolute inset-0 bg-gradient-to-t from-[#050507] via-transparent to-transparent" />
              <div v-if="product?.status !== 'active'" class="absolute inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center">
                <span class="px-4 py-2 bg-red-500/10 border border-red-400/20 rounded-lg text-red-400 text-sm font-semibold">{{ $t('product.notAvailable') }}</span>
              </div>
            </div>
            <div class="absolute top-4 right-4 flex flex-col gap-2">
              <span v-if="isAuto" class="px-3 py-1.5 text-[10px] font-mono font-semibold bg-cyan-500/15 text-cyan-400 border border-cyan-400/30 rounded-lg backdrop-blur-sm flex items-center gap-1.5">
                <Zap class="w-3 h-3 fill-cyan-400/20" /> {{ $t('product.autoDelivery') }}
              </span>
              <span v-if="product?.status === 'active'" class="px-3 py-1.5 text-[10px] font-mono font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-400/20 rounded-lg backdrop-blur-sm flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" /> {{ $t('product.inStock') }}
              </span>
            </div>
          </div>
        </div>

        <!-- Product Info -->
        <div class="lg:col-span-7 space-y-6">
          <div>
            <div class="flex items-center gap-2 mb-3">
              <span class="text-[11px] font-mono text-cyan-400/70 uppercase tracking-wider">{{ product?.category?.name || $t('common.general') }}</span>
              <span class="w-1 h-1 rounded-full bg-white/20" />
              <span class="text-[11px] font-mono text-white/40 uppercase tracking-wider">{{ product?.provider?.name || $t('app.name') }}</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-white leading-tight font-display">{{ product?.name }}</h1>
          </div>

          <!-- Price Card -->
          <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-32 h-32 bg-cyan-500/5 rounded-full blur-3xl" />
            <div class="relative flex items-end justify-between">
              <div>
                <p class="text-[11px] font-mono text-white/40 uppercase tracking-wider mb-1">{{ $t('product.price') }}</p>
                <div class="flex items-baseline gap-2">
                  <span class="text-4xl font-bold text-white font-mono tracking-tight">$ {{ totalPrice }}</span>
                  <span v-if="quantity > 1" class="text-sm text-white/40">($ {{ fmtMoney(product?.price) }} {{ $t('product.perUnit') }})</span>
                </div>
              </div>
              <div v-if="product?.cost_price" class="text-right">
                <p class="text-[10px] font-mono text-white/30 uppercase tracking-wider">سعر التكلفة</p>
                <p class="text-sm font-mono text-white/40">$ {{ fmtMoney(product?.cost_price) }}</p>
              </div>
            </div>
          </div>

          <!-- Quantity Selector -->
          <div v-if="qtyValues.length" class="space-y-3">
            <label class="text-sm font-medium text-white/80">{{ $t('product.quantity') }}</label>
            <div class="flex flex-wrap gap-2">
              <button v-for="v in qtyValues" :key="v" @click="setQty(v)" class="px-4 py-2 text-sm rounded-xl border transition-all active:scale-95" :class="quantity === v ? 'bg-cyan-400/10 text-cyan-400 border-cyan-400/30' : 'bg-white/[0.02] text-white/50 border-white/5 hover:border-white/10 hover:text-white'">
                {{ v }}
              </button>
            </div>
          </div>
          <div v-else class="flex items-center gap-4">
            <label class="text-sm font-medium text-white/80">{{ $t('product.quantity') }}</label>
            <div class="flex items-center gap-3 bg-white/[0.02] border border-white/5 rounded-xl p-1">
              <button @click="decQty" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 text-white/60 hover:text-white hover:bg-white/10 transition-colors"><Minus class="w-4 h-4" /></button>
              <span class="w-8 text-center font-mono font-bold text-white">{{ quantity }}</span>
              <button @click="incQty" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 text-white/60 hover:text-white hover:bg-white/10 transition-colors"><Plus class="w-4 h-4" /></button>
            </div>
          </div>

          <!-- Dynamic Fields -->
          <div v-if="params.length" class="space-y-4">
            <div class="h-px bg-white/5" />
            <p class="text-sm font-medium text-white/80">{{ $t('product.requiredInfo') }}</p>
            <div v-for="param in params" :key="param" class="space-y-1.5">
              <label class="text-xs font-mono text-white/40 uppercase tracking-wider">{{ param }}</label>
              <input v-model="formValues[param]" type="text" :placeholder="`${$t('product.enterDetails')}: ${param}`" class="w-full h-12 px-4 bg-white/[0.02] border border-white/10 rounded-xl text-sm text-white placeholder:text-white/25 focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.03] transition-all" />
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button @click="placeOrder" :disabled="product?.status !== 'active' || orderLoading" class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-4 text-sm font-bold rounded-xl transition-all active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed" :class="product?.status === 'active' ? 'bg-cyan-400 text-black hover:bg-cyan-300 shadow-[0_0_30px_-5px_rgba(34,211,238,0.3)]' : 'bg-white/5 text-white/30 border border-white/5'">
              <Zap v-if="!orderLoading" class="w-4 h-4" />
              <div v-else class="w-4 h-4 border-2 border-black/30 border-t-black rounded-full animate-spin" />
              {{ orderLoading ? $t('product.processing') : $t('product.buyNowAction') }}
            </button>
            <div v-if="!user" class="flex-1 flex items-center justify-center gap-2 px-6 py-4 text-sm text-white/40 border border-white/5 rounded-xl bg-white/[0.02]">
              <User class="w-4 h-4" /> {{ $t('product.loginToBuy') }}
            </div>
          </div>

          <!-- Trust Badges -->
          <div class="flex flex-wrap items-center gap-4 pt-2">
            <div class="flex items-center gap-2 text-xs text-white/30">
              <ShieldCheck class="w-3.5 h-3.5 text-emerald-400" />
              <span>{{ $t('app.features.securePayment') }}</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-white/30">
              <Zap class="w-3.5 h-3.5 text-cyan-400" />
              <span>{{ $t('app.features.instantDelivery') }}</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-white/30">
              <Clock class="w-3.5 h-3.5 text-amber-400" />
              <span>{{ $t('app.features.support247') }}</span>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
