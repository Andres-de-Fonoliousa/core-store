<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useUiStore } from '@/stores/uiStore';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { useAuthStore } from '@/stores/authStore';
import { parseApiError } from '@/lib/errors';
import { safeJsonLd, buildProductJsonLd, buildBreadcrumbJsonLd, buildStoreJsonLd, defaultDescription, siteName } from '@/lib/seo';
import JsonLdScript from '@/components/JsonLdScript.vue';
import AppNavbar from '@/components/AppNavbar.vue';
import AppFooter from '@/components/AppFooter.vue';
import type { Product } from '@/types/models';
import {
  ShoppingCart, Zap, ArrowLeft, ShieldCheck, Clock, Package,
  Gamepad2, Monitor, Sparkles, Check, AlertTriangle, Loader2,
  ChevronRight, CreditCard, Cpu, Tag, Star
} from 'lucide-vue-next';

const page = usePage();
const api = useApi();
const { t } = useI18n();
const ui = useUiStore();
const toast = useToast();
const authStore = useAuthStore();

const product = ref<Product | null>(null);
const quantity = ref(1);
const params = ref<Record<string, string>>({});
const submitting = ref(false);
const isLoading = ref(true);
const fetchError = ref<string | null>(null);
const fieldErrors = ref<Record<string, string[]>>({});

const productId = Number((page.props as any)?.productId ?? (page.props as any)?.product ?? 0);

const user = computed(() => authStore.user);
const canPurchase = computed(() => !!user.value);

function toNum(v: string | number | null | undefined): number {
  if (v == null) return 0;
  const n = typeof v === 'string' ? parseFloat(v) : v;
  return isNaN(n) ? 0 : n;
}
function fmtMoney(v: string | number | null | undefined): string {
  return toNum(v).toFixed(2);
}

async function loadProduct() {
  isLoading.value = true;
  fetchError.value = null;
  try {
    const { data } = await api.get(`/products/${productId}`);
    product.value = data;
    if (product.value) {
      product.value.qty_values = product.value.qty_values?.length ? product.value.qty_values : [1];
      quantity.value = product.value.qty_values[0] ?? 1;
      product.value.params?.forEach((name: string) => {
        params.value[name] = '';
      });
    }
  } catch (error: any) {
    fetchError.value = parseApiError(error);
    toast.error(fetchError.value);
  } finally {
    isLoading.value = false;
  }
}

async function placeOrder() {
  if (!product.value) return;

  if (!canPurchase.value) {
    toast.warning(t('product.loginToBuy'));
    setTimeout(() => { window.location.href = '/login?redirect=/products/' + productId; }, 800);
    return;
  }

  const invalidParam = product.value.params?.find((name) => !params.value[name]?.trim());
  if (invalidParam) {
    toast.error(t('product.pleaseFillParam', { param: invalidParam }));
    return;
  }

  submitting.value = true;
  fieldErrors.value = {};

  try {
    const payload = {
      product_id: product.value.id,
      quantity: quantity.value,
      playerId: params.value[product.value.params[0]] ?? '',
      params: params.value,
    };
    const { data } = await api.post('/orders', payload);
    toast.success(t('product.orderPlaced'));
    setTimeout(() => router.visit(`/orders/${data.id}`), 800);
  } catch (err: any) {
    if (err?.response?.status === 422 && err?.response?.data?.errors) {
      fieldErrors.value = err.response.data.errors;
    }
    toast.error(parseApiError(err));
  } finally {
    submitting.value = false;
  }
}

loadProduct();

const seoTitle = computed(() => product.value ? `${product.value.name} | ${siteName}` : `${siteName} | ${defaultDescription.slice(0, 40)}`);
const seoDescription = computed(() => product.value?.description ?? defaultDescription);
const seoImage = computed(() => {
  if (!product.value?.image) return undefined;
  const base = window.location.origin;
  return product.value.image.startsWith('http') ? product.value.image : `${base}/storage/${product.value.image}`;
});
const canonicalUrl = computed(() => product.value ? `${window.location.origin}/products/${product.value.id}` : window.location.href);
const productJsonLd = computed(() => product.value ? safeJsonLd(buildProductJsonLd(product.value)) : '');
const breadcrumbJsonLd = computed(() => {
  const items = [{ name: t('nav.home'), url: window.location.origin }];
  if (product.value?.category?.name) {
    items.push({ name: product.value.category.name, url: `${window.location.origin}/browse/${product.value.category_id}` });
  }
  items.push({ name: product.value?.name ?? t('common.product'), url: canonicalUrl.value });
  return safeJsonLd(buildBreadcrumbJsonLd(items));
});
const storeJsonLd = computed(() => safeJsonLd(buildStoreJsonLd()));
</script>

<template>
  <Head :title="product?.name ?? $t('common.product')">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />

    <meta name="description" :content="seoDescription" />
    <link rel="canonical" :href="canonicalUrl" />

    <meta property="og:type" content="product" />
    <meta property="og:site_name" :content="$t('app.name')" />
    <meta property="og:title" :content="seoTitle" />
    <meta property="og:description" :content="seoDescription" />
    <meta property="og:url" :content="canonicalUrl" />
    <meta v-if="seoImage" property="og:image" :content="seoImage" />
    <meta property="og:locale" :content="ui.locale === 'ar' ? 'ar_SY' : 'en_US'" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" :content="seoTitle" />
    <meta name="twitter:description" :content="seoDescription" />
    <meta v-if="seoImage" name="twitter:image" :content="seoImage" />

    <JsonLdScript v-if="productJsonLd" :data="productJsonLd" />
    <JsonLdScript :data="breadcrumbJsonLd" />
    <JsonLdScript :data="storeJsonLd" />
  </Head>

  <div
    :dir="ui.isRtl ? 'rtl' : 'ltr'"
    class="min-h-screen bg-[#050507] text-white relative overflow-x-hidden"
    style="font-family: 'Cairo', 'Space Grotesk', sans-serif; margin-left: calc(-50vw + 50%); margin-right: calc(-50vw + 50%); width: 100vw;"
  >
    <!-- Background -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]" />
      <div class="absolute top-[-10%] left-1/2 -translate-x-1/2 w-[80%] h-[40%] bg-cyan-500/10 blur-[120px] rounded-full" />
    </div>

    <!-- ═══ Navbar ═══ -->
    <AppNavbar />

    <!-- ═══ Back Nav ═══ -->
    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-10 xl:px-16 pt-24 pb-4">
      <Link href="/" class="inline-flex items-center gap-2 text-sm text-white/50 hover:text-cyan-400 transition-colors group">
        <div class="w-8 h-8 rounded-lg border border-white/10 bg-white/5 flex items-center justify-center group-hover:border-cyan-400/30 transition-colors">
          <ArrowLeft class="w-4 h-4" />
        </div>
        <span>{{ $t('common.backToStore') }}</span>
      </Link>
    </div>

    <!-- ═══ Loading State ═══ -->
    <div v-if="isLoading" class="relative z-10 w-full px-4 sm:px-6 lg:px-10 xl:px-16 py-12">
      <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr] max-w-7xl mx-auto">
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 space-y-4 animate-pulse">
          <div class="aspect-[4/3] rounded-2xl bg-white/5" />
          <div class="h-4 bg-white/5 rounded w-1/3" />
          <div class="h-8 bg-white/5 rounded w-3/4" />
          <div class="h-20 bg-white/5 rounded" />
        </div>
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 space-y-4 animate-pulse">
          <div class="h-12 bg-white/5 rounded" />
          <div class="h-8 bg-white/5 rounded w-1/2" />
          <div class="h-32 bg-white/5 rounded" />
          <div class="h-12 bg-white/5 rounded" />
        </div>
      </div>
    </div>

    <!-- ═══ Error State ═══ -->
    <div v-else-if="fetchError" class="relative z-10 w-full px-4 sm:px-6 lg:px-10 xl:px-16 py-24">
      <div class="max-w-md mx-auto text-center">
        <div class="w-20 h-20 rounded-2xl bg-red-500/5 border border-red-500/10 flex items-center justify-center mb-5 mx-auto">
          <AlertTriangle class="w-8 h-8 text-red-500/50" />
        </div>
        <h3 class="text-xl font-semibold text-white mb-2">{{ $t('common.errorOccurred') }}</h3>
        <p class="text-sm text-white/50 mb-6">{{ fetchError }}</p>
        <button @click="loadProduct" class="inline-flex items-center gap-2 px-6 py-3 bg-cyan-500/10 text-cyan-400 border border-cyan-400/20 rounded-xl hover:bg-cyan-500/20 transition-all active:scale-95">
          <Loader2 class="w-4 h-4" /> {{ $t('common.retry') }}
        </button>
      </div>
    </div>

    <!-- ═══ Product Content ═══ -->
    <div v-else-if="product" class="relative z-10 w-full px-4 sm:px-6 lg:px-10 xl:px-16 py-8 pb-24">
      <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr] max-w-7xl mx-auto">

        <!-- ═══ Left: Image & Info ═══ -->
        <div class="space-y-6">
          <!-- Image Card -->
          <div class="relative bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden group">
            <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-cyan-400/30 to-transparent" />
            <div class="absolute top-4 left-4 w-5 h-5 border-t border-l border-cyan-400/20" />
            <div class="absolute top-4 right-4 w-5 h-5 border-t border-r border-cyan-400/20" />
            <div class="absolute bottom-4 left-4 w-5 h-5 border-b border-l border-cyan-400/20" />
            <div class="absolute bottom-4 right-4 w-5 h-5 border-b border-r border-cyan-400/20" />

            <div class="aspect-[4/3] overflow-hidden bg-white/5">
              <img
                :src="product.image || '/placeholder-product.png'"
                :alt="product.name"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                @error="($event.target as HTMLImageElement).src = '/placeholder-product.png'"
              />
            </div>

            <!-- Status Badges -->
            <div class="absolute top-6 right-6 flex flex-col gap-2">
              <span v-if="product.status === 'active'" class="px-3 py-1.5 text-[11px] font-mono font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-400/20 rounded-lg backdrop-blur-sm flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" /> {{ $t('product.inStock') }}
              </span>
              <span v-else class="px-3 py-1.5 text-[11px] font-mono font-semibold bg-red-500/10 text-red-400 border border-red-400/20 rounded-lg backdrop-blur-sm">
                {{ $t('product.outOfStock') }}
              </span>
            </div>
            <div v-if="product.is_auto" class="absolute top-6 left-6">
              <span class="px-3 py-1.5 text-[11px] font-mono font-semibold bg-cyan-500/15 text-cyan-400 border border-cyan-400/30 rounded-lg backdrop-blur-sm flex items-center gap-1.5 shadow-lg shadow-cyan-500/10">
                <Zap class="w-3.5 h-3.5 fill-cyan-400/20" /> {{ $t('product.badgeAuto') }}
              </span>
            </div>
          </div>

          <!-- Info Card -->
          <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 relative">
            <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-cyan-400/20 to-transparent" />

            <div class="flex flex-wrap items-center gap-2 mb-4">
              <span class="px-3 py-1.5 text-xs font-medium bg-cyan-500/10 text-cyan-400 border border-cyan-400/20 rounded-lg flex items-center gap-1.5">
                <Cpu class="w-3.5 h-3.5" /> {{ product.provider?.name || $t('app.name') }}
              </span>
              <span class="px-3 py-1.5 text-xs font-medium bg-purple-500/10 text-purple-400 border border-purple-400/20 rounded-lg flex items-center gap-1.5">
                <Tag class="w-3.5 h-3.5" /> {{ product.category?.name || $t('common.general') }}
              </span>
              <span v-if="product.is_auto" class="px-3 py-1.5 text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-400/20 rounded-lg flex items-center gap-1.5">
                <Check class="w-3.5 h-3.5" /> {{ $t('product.autoShippingBadge') }}
              </span>
            </div>

            <h1 class="text-2xl md:text-3xl font-bold text-white mb-3" style="font-family: 'Space Grotesk', sans-serif;">
              {{ product.name }}
            </h1>

            <p class="text-sm text-white/50 leading-relaxed mb-6">
              {{ product.params?.length ? $t('product.enterDetails') : $t('product.clickBuy') }}
            </p>

            <!-- Features -->
            <div class="grid grid-cols-2 gap-3">
              <div class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.02] border border-white/5">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center flex-shrink-0">
                  <Clock class="w-4 h-4 text-cyan-400" />
                </div>
                <div>
                  <div class="text-xs font-semibold text-white">{{ $t('app.features.instantDelivery') }}</div>
                  <div class="text-[10px] text-white/40">{{ $t('product.within30s') }}</div>
                </div>
              </div>
              <div class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.02] border border-white/5">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center flex-shrink-0">
                  <ShieldCheck class="w-4 h-4 text-emerald-400" />
                </div>
                <div>
                  <div class="text-xs font-semibold text-white">{{ $t('app.features.securePayment') }}</div>
                  <div class="text-[10px] text-white/40">{{ $t('app.features.sslEncrypted') }}</div>
                </div>
              </div>
              <div class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.02] border border-white/5">
                <div class="w-8 h-8 rounded-lg bg-purple-500/10 border border-purple-400/20 flex items-center justify-center flex-shrink-0">
                  <Package class="w-4 h-4 text-purple-400" />
                </div>
                <div>
                  <div class="text-xs font-semibold text-white">{{ $t('product.liveStockTitle') }}</div>
                  <div class="text-[10px] text-white/40">{{ $t('product.realTimeUpdate') }}</div>
                </div>
              </div>
              <div class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.02] border border-white/5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-400/20 flex items-center justify-center flex-shrink-0">
                  <Star class="w-4 h-4 text-amber-400" />
                </div>
                <div>
                  <div class="text-xs font-semibold text-white">{{ $t('product.qualityGuaranteeTitle') }}</div>
                  <div class="text-[10px] text-white/40">{{ $t('product.trusted') }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ═══ Right: Purchase Card ═══ -->
        <div class="space-y-6">
          <div class="sticky top-24 bg-white/[0.02] border border-white/5 rounded-2xl p-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-cyan-400/30 to-transparent" />
            <div class="absolute top-3 left-3 w-5 h-5 border-t border-l border-cyan-400/20" />
            <div class="absolute top-3 right-3 w-5 h-5 border-t border-r border-cyan-400/20" />
            <div class="absolute bottom-3 left-3 w-5 h-5 border-b border-l border-cyan-400/20" />
            <div class="absolute bottom-3 right-3 w-5 h-5 border-b border-r border-cyan-400/20" />

            <!-- Price -->
            <div class="flex items-center justify-between mb-6">
              <div>
                <p class="text-xs text-white/40 font-mono mb-1">{{ $t('product.price') }}</p>
                <p class="text-4xl font-bold text-white tracking-tight font-mono">
                  $ {{ fmtMoney(product.price) }}
                </p>
              </div>
              <div v-if="product.cost_price && toNum(product.cost_price) < toNum(product.price)" class="text-right">
                <p class="text-xs text-white/30 line-through font-mono">$ {{ fmtMoney(product.cost_price) }}</p>
                <p class="text-[10px] text-emerald-400 font-mono">{{ $t('product.savePercent', { pct: Math.round((1 - toNum(product.cost_price)/toNum(product.price)) * 100) }) }}</p>
              </div>
            </div>

            <!-- Divider -->
            <div class="h-px bg-gradient-to-r from-transparent via-white/10 to-transparent mb-6" />

            <!-- Quantity -->
            <div class="mb-6">
              <label class="block text-xs font-medium text-white/60 mb-3">{{ $t('product.selectQty') }}</label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="qty in product.qty_values || [1]"
                  :key="qty"
                  @click="quantity = qty"
                  class="px-4 py-2.5 text-sm font-mono font-semibold rounded-xl border transition-all duration-200 active:scale-95"
                  :class="quantity === qty
                    ? 'bg-cyan-400/10 text-cyan-400 border-cyan-400/30 shadow-[0_0_15px_-5px_rgba(34,211,238,0.2)]'
                    : 'bg-white/[0.02] text-white/60 border-white/10 hover:border-cyan-400/30 hover:text-white'"
                >
                  {{ qty }}
                </button>
              </div>
            </div>

            <!-- Params -->
            <div v-if="product.params?.length" class="space-y-4 mb-6">
              <div v-for="param in product.params" :key="param" class="space-y-2">
                <label class="block text-xs font-medium text-white/60 flex items-center gap-2">
                  <Gamepad2 class="w-3.5 h-3.5 text-cyan-400/70" />
                  {{ param }}
                  <span class="text-red-400/70">*</span>
                </label>
                <input
                  type="text"
                  :placeholder="param"
                  v-model="params[param]"
                  class="w-full px-4 py-3 bg-white/[0.03] border rounded-xl text-white text-sm placeholder:text-white/30 focus:outline-none focus:ring-2 transition-all"
                  :class="fieldErrors['params.' + param]?.length
                    ? 'border-red-400/50 focus:border-red-400 focus:ring-red-400/10'
                    : 'border-white/10 focus:border-cyan-400/50 focus:ring-cyan-400/10'"
                />
                <p v-if="fieldErrors['params.' + param]?.length" class="text-xs text-red-400 font-mono">
                  {{ fieldErrors['params.' + param][0] }}
                </p>
              </div>
            </div>

            <!-- Total -->
            <div class="flex items-center justify-between p-4 rounded-xl bg-white/[0.02] border border-white/5 mb-6">
              <span class="text-sm text-white/60">{{ $t('order.total') }}</span>
              <span class="text-2xl font-bold text-cyan-400 font-mono tracking-tight">
                $ {{ fmtMoney(toNum(product.price) * quantity) }}
              </span>
            </div>

            <!-- Buy Button -->
            <button
              type="button"
              @click="placeOrder"
              :disabled="submitting || product.status !== 'active'"
              class="relative w-full py-4 rounded-xl text-sm font-bold transition-all duration-300 overflow-hidden group active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
              :class="product.status === 'active'
                ? 'bg-cyan-400 text-black hover:bg-cyan-300 hover:shadow-[0_0_30px_-5px_rgba(34,211,238,0.5)]'
                : 'bg-white/5 text-white/30 border border-white/5'"
            >
              <span class="relative z-10 flex items-center justify-center gap-2">
                <Loader2 v-if="submitting" class="w-4 h-4 animate-spin" />
                <Zap v-else-if="product.status === 'active'" class="w-4 h-4" />
                <ShoppingCart v-else class="w-4 h-4" />
                {{ submitting ? $t('product.processing') : product.status === 'active' ? $t('product.buyNow') : $t('product.notAvailable') }}
              </span>
            </button>

            <!-- Trust badges -->
            <div class="flex items-center justify-center gap-4 mt-5">
              <div class="flex items-center gap-1.5 text-[10px] text-white/40">
                <ShieldCheck class="w-3 h-3 text-emerald-400/60" />
                <span>{{ $t('app.features.securePayment') }}</span>
              </div>
              <div class="w-px h-3 bg-white/10" />
              <div class="flex items-center gap-1.5 text-[10px] text-white/40">
                <Clock class="w-3 h-3 text-cyan-400/60" />
                <span>{{ $t('app.features.instantDelivery') }}</span>
              </div>
              <div class="w-px h-3 bg-white/10" />
              <div class="flex items-center gap-1.5 text-[10px] text-white/40">
                <CreditCard class="w-3 h-3 text-purple-400/60" />
                <span>{{ $t('product.balanceLabel') }}</span>
              </div>
            </div>

            <!-- Login prompt for guests -->
            <div v-if="!user" class="mt-5 p-4 rounded-xl bg-cyan-500/5 border border-cyan-400/10 text-center">
              <p class="text-xs text-white/60 mb-2">{{ $t('common.mustLoginToBuy') }}</p>
              <Link href="/login" class="inline-flex items-center gap-1.5 text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors">
                {{ $t('nav.login') }} <ChevronRight class="w-3 h-3" />
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <AppFooter />
  </div>
</template>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>