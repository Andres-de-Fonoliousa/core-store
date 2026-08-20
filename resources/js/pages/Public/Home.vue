<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import {
  Search, SlidersHorizontal, Grid2x2, List, Tag,
  ChevronLeft, ShoppingCart, Zap,
  X, Package, Loader2,
  Home, ChevronRight, Layers,
  Gamepad2, Sparkles, Music, Gift, Monitor,
  Headphones, Film, Globe, Smartphone, Star,
  Shield, BookOpen, Cloud, Wifi,
} from 'lucide-vue-next';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import { safeJsonLd, buildBreadcrumbJsonLd, buildWebSiteJsonLd, buildStoreJsonLd, defaultDescription, siteName } from '@/lib/seo';
import JsonLdScript from '@/components/JsonLdScript.vue';
import AppNavbar from '@/components/AppNavbar.vue';
import AppFooter from '@/components/AppFooter.vue';

const props = defineProps<{
  categoryId?: number;
  categoryName?: string;
  categoryAncestors?: { id: number; name: string }[];
  categoryParentId?: number | null;
}>();

const { t } = useI18n();
const api = useApi();
const toast = useToast();

const loading = ref(false);
const productLoading = ref(false);
const searchQuery = ref('');
const sortBy = ref('popular');
const viewMode = ref<'grid' | 'list'>('grid');
const priceRange = ref(100);
const page = ref(1);
const pagination = ref<any>(null);
const mobileCatOpen = ref(false);
const sidebarCats = ref<any[]>([]);
const products = ref<any[]>([]);
const currentCat = ref<{ id: number; name: string } | null>(
  props.categoryId && props.categoryName ? { id: props.categoryId, name: props.categoryName } : null
);
const ancestors = ref<{ id: number; name: string }[]>(props.categoryAncestors ?? []);

const isRoot = computed(() => !props.categoryId);
const hasSubcategories = computed(() => sidebarCats.value.length > 0);

const CAT_COLORS = [
  { from: 'rgba(16,185,129,0.25)', to: 'rgba(16,185,129,0.05)', border: 'rgba(16,185,129,0.35)', shadow: 'rgba(16,185,129,0.15)', icon: Gamepad2 },
  { from: 'rgba(244,63,94,0.25)', to: 'rgba(244,63,94,0.05)', border: 'rgba(244,63,94,0.35)', shadow: 'rgba(244,63,94,0.15)', icon: Sparkles },
  { from: 'rgba(139,92,246,0.25)', to: 'rgba(139,92,246,0.05)', border: 'rgba(139,92,246,0.35)', shadow: 'rgba(139,92,246,0.15)', icon: Music },
  { from: 'rgba(59,130,246,0.25)', to: 'rgba(59,130,246,0.05)', border: 'rgba(59,130,246,0.35)', shadow: 'rgba(59,130,246,0.15)', icon: Monitor },
  { from: 'rgba(245,158,11,0.25)', to: 'rgba(245,158,11,0.05)', border: 'rgba(245,158,11,0.35)', shadow: 'rgba(245,158,11,0.15)', icon: Gift },
  { from: 'rgba(34,211,238,0.25)', to: 'rgba(34,211,238,0.05)', border: 'rgba(34,211,238,0.35)', shadow: 'rgba(34,211,238,0.15)', icon: Globe },
  { from: 'rgba(217,70,239,0.25)', to: 'rgba(217,70,239,0.05)', border: 'rgba(217,70,239,0.35)', shadow: 'rgba(217,70,239,0.15)', icon: Headphones },
  { from: 'rgba(132,204,22,0.25)', to: 'rgba(132,204,22,0.05)', border: 'rgba(132,204,22,0.35)', shadow: 'rgba(132,204,22,0.15)', icon: Smartphone },
  { from: 'rgba(249,115,22,0.25)', to: 'rgba(249,115,22,0.05)', border: 'rgba(249,115,22,0.35)', shadow: 'rgba(249,115,22,0.15)', icon: Star },
  { from: 'rgba(20,184,166,0.25)', to: 'rgba(20,184,166,0.05)', border: 'rgba(20,184,166,0.35)', shadow: 'rgba(20,184,166,0.15)', icon: Shield },
  { from: 'rgba(99,102,241,0.25)', to: 'rgba(99,102,241,0.05)', border: 'rgba(99,102,241,0.35)', shadow: 'rgba(99,102,241,0.15)', icon: BookOpen },
  { from: 'rgba(236,72,153,0.25)', to: 'rgba(236,72,153,0.05)', border: 'rgba(236,72,153,0.35)', shadow: 'rgba(236,72,153,0.15)', icon: Film },
  { from: 'rgba(14,165,233,0.25)', to: 'rgba(14,165,233,0.05)', border: 'rgba(14,165,233,0.35)', shadow: 'rgba(14,165,233,0.15)', icon: Cloud },
  { from: 'rgba(168,85,247,0.25)', to: 'rgba(168,85,247,0.05)', border: 'rgba(168,85,247,0.35)', shadow: 'rgba(168,85,247,0.15)', icon: Wifi },
];

function getCatStyle(cat: any) {
  return CAT_COLORS[cat.id % CAT_COLORS.length];
}

const filteredProducts = computed(() => {
  let result = products.value;
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(p => p.name.toLowerCase().includes(q));
  }
  if (sortBy.value === 'price_asc') result = [...result].sort((a, b) => Number(a.price) - Number(b.price));
  if (sortBy.value === 'price_desc') result = [...result].sort((a, b) => Number(b.price) - Number(a.price));
  return result;
});

function navigateTo(catId: number) {
  mobileCatOpen.value = false;
  router.visit(`/browse/${catId}`);
}

function goHome() {
  mobileCatOpen.value = false;
  router.visit('/browse');
}

function clearFilters() {
  searchQuery.value = '';
  sortBy.value = 'popular';
  priceRange.value = 100;
}

function goToPage(p: number) {
  page.value = p;
  fetchProducts();
}

async function fetchSidebar() {
  try {
    const params: Record<string, any> = { per_page: 50 };
    if (props.categoryId) {
      params.parent_id = props.categoryId;
    } else {
      params.parent_id = 'null';
    }
    const { data } = await api.get('/categories', { params });
    sidebarCats.value = data.data;
  } catch (err) {
    toast.error(parseApiError(err));
    sidebarCats.value = [];
  }
}

async function fetchProducts() {
  productLoading.value = true;
  try {
    const params: Record<string, any> = { per_page: 12, page: page.value };
    if (props.categoryId) params.category_id = props.categoryId;
    const { data } = await api.get('/products', { params });
    products.value = data.data;
    pagination.value = data.meta ?? null;
  } catch (err) {
    toast.error(parseApiError(err));
    products.value = [];
  } finally {
    productLoading.value = false;
  }
}

const showProductView = computed(() => !isRoot.value && !hasSubcategories.value && !loading.value);

onMounted(async () => {
  loading.value = true;
  if (isRoot.value || (props.categoryId && hasSubcategories.value)) {
    await fetchSidebar();
  } else {
    await Promise.all([fetchSidebar(), fetchProducts()]);
  }
  loading.value = false;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('revealed');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.05 });

  setTimeout(() => {
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
  }, 50);
});

const seoTitle = computed(() => {
  if (currentCat.value) return `${currentCat.value.name} | ${siteName}`;
  return siteName;
});
const seoDescription = computed(() => {
      if (currentCat.value) return t('browse.description', { name: currentCat.value.name, store: siteName });
  return defaultDescription;
});
const canonicalUrl = computed(() => {
  if (currentCat.value) return `${window.location.origin}/browse/${currentCat.value.id}`;
  return `${window.location.origin}/browse`;
});
const breadcrumbJsonLd = computed(() => {
  const items = [{ name: siteName, url: window.location.origin }];
  for (const a of ancestors.value) {
    items.push({ name: a.name, url: `${window.location.origin}/browse/${a.id}` });
  }
  if (currentCat.value) {
    items.push({ name: currentCat.value.name, url: canonicalUrl.value });
  }
  return safeJsonLd(buildBreadcrumbJsonLd(items));
});
const websiteJsonLd = computed(() => safeJsonLd(buildWebSiteJsonLd()));
const storeJsonLd = computed(() => safeJsonLd(buildStoreJsonLd()));
</script>

<template>
  <Head :title="currentCat?.name || $t('nav.catalog')">
    <meta name="description" :content="seoDescription" />
    <link rel="canonical" :href="canonicalUrl" />

    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="CoreS" />
    <meta property="og:title" :content="seoTitle" />
    <meta property="og:description" :content="seoDescription" />
    <meta property="og:url" :content="canonicalUrl" />
    <meta property="og:locale" :content="$t('browse.locale')" />

    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" :content="seoTitle" />
    <meta name="twitter:description" :content="seoDescription" />

    <JsonLdScript :data="breadcrumbJsonLd" />
    <JsonLdScript :data="websiteJsonLd" />
    <JsonLdScript :data="storeJsonLd" />
  </Head>

  <div class="fixed inset-0 bg-background text-foreground overflow-y-auto overflow-x-hidden font-sans">
    <div class="fixed inset-0 bg-grid pointer-events-none z-0 opacity-50" />
    <div class="fixed inset-0 pointer-events-none z-0" style="background: radial-gradient(ellipse at 50% 0%, hsl(186 100% 55% / 0.03) 0%, transparent 50%)" />

    <!-- Navbar -->
    <AppNavbar />

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 pt-24 sm:pt-28 pb-12">

      <!-- Page Header -->
      <div class="mb-8 sm:mb-10">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 sm:gap-6">
          <div class="min-w-0">
            <div class="flex items-center gap-1.5 text-[10px] sm:text-xs font-mono text-muted-foreground/60 mb-2 flex-wrap">
              <button @click="goHome" class="hover:text-primary transition-colors flex items-center gap-1">
                <Home class="w-2.5 h-2.5 sm:w-3 sm:h-3" /> {{ $t('nav.home') }}
              </button>
              <template v-if="!isRoot">
                <ChevronLeft class="w-2.5 h-2.5 sm:w-3 sm:h-3 shrink-0" />
                <button v-for="a in ancestors" :key="a.id" @click="router.visit(`/browse/${a.id}`)" class="hover:text-primary transition-colors truncate max-w-[100px] sm:max-w-[150px]">
                  {{ a.name }}
                </button>
                <template v-if="currentCat">
                  <ChevronLeft class="w-2.5 h-2.5 sm:w-3 sm:h-3 shrink-0" />
                  <span class="text-primary truncate max-w-[120px] sm:max-w-[200px]">{{ currentCat.name }}</span>
                </template>
              </template>
            </div>
            <span class="text-[10px] sm:text-xs font-mono text-primary tracking-[0.2em] uppercase">
              {{ isRoot ? $t('nav.products') : hasSubcategories ? $t('product.selectQty') : $t('common.products') }}
            </span>
            <h1 class="text-2xl sm:text-4xl md:text-5xl font-bold tracking-tight mt-1 sm:mt-2 text-white leading-tight">
              <template v-if="isRoot">
                {{ $t('browse.digitalMarket', { name: $t('app.name') }) }}
              </template>
              <template v-else>
                {{ currentCat?.name }}
              </template>
            </h1>
            <p v-if="isRoot" class="text-xs sm:text-sm text-muted-foreground mt-1 sm:mt-2 max-w-lg">
              {{ $t('app.tagline') }}
            </p>
            <p v-else-if="!hasSubcategories" class="text-xs sm:text-sm text-muted-foreground mt-1 sm:mt-2">
              {{ $t('browse.browseProducts') }}
            </p>
          </div>
          <div v-if="!isRoot && hasSubcategories" class="relative w-full sm:w-72 shrink-0">
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="$t('browse.searchCategory')"
              class="w-full bg-white/[0.02] border border-white/10 rounded-lg py-2.5 sm:py-3 px-3 sm:px-4 pr-9 sm:pr-10 text-xs sm:text-sm text-white placeholder:text-muted-foreground/50 focus:outline-none focus:border-primary/40 focus:ring-2 focus:ring-primary/10 transition-all font-sans"
            />
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 sm:w-4 sm:h-4 text-muted-foreground/50" />
          </div>
        </div>
      </div>

      <!-- ===== CATEGORY CARDS VIEW (root + non-leaf categories) ===== -->
      <template v-if="!showProductView">
        <div class="grid lg:grid-cols-[220px_1fr] gap-8">
          <!-- Sidebar navigation -->
          <aside class="hidden lg:block">
            <div class="sticky top-28 space-y-5">
              <button
                v-if="!isRoot"
                @click="goHome"
                class="w-full flex items-center gap-2 px-4 py-3 rounded-xl bg-white/[0.02] border border-white/5 text-sm text-muted-foreground hover:text-white hover:bg-white/[0.04] hover:border-primary/20 transition-all backdrop-blur-xl"
              >
                <ChevronRight class="w-4 h-4 text-primary" />
                {{ $t('nav.allCategories') }}
              </button>

              <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-4 backdrop-blur-xl">
                <h3 class="text-xs font-semibold text-white tracking-wide mb-3">
                  {{ isRoot ? $t('browse.mainCategories') : $t('browse.subcategories') }}
                </h3>
                <div class="flex flex-col gap-0.5">
                  <button
                    v-for="cat in sidebarCats"
                    :key="cat.id"
                    @click="navigateTo(cat.id)"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs transition-all text-right w-full"
                    :class="cat.id === props.categoryId
                      ? 'bg-primary/10 text-primary font-semibold'
                      : 'text-muted-foreground hover:text-white hover:bg-white/[0.03]'"
                  >
                    <div class="w-5 h-5 rounded flex items-center justify-center shrink-0"
                      :style="{
                        background: `linear-gradient(135deg, ${getCatStyle(cat).from}, ${getCatStyle(cat).to})`,
                      }">
                      <component :is="getCatStyle(cat).icon" class="w-3 h-3" :style="{ color: getCatStyle(cat).border.replace('0.35', '1') }" />
                    </div>
                    <span class="truncate">{{ cat.name }}</span>
                    <span class="mr-auto text-[10px] font-mono opacity-50">{{ cat.products_count }}</span>
                  </button>
                </div>
              </div>
            </div>
          </aside>

          <!-- Category grid -->
          <main>
            <div class="flex items-center justify-between mb-6">
              <p class="text-xs sm:text-sm text-muted-foreground">
                {{ $t('browse.categoriesCount', { n: sidebarCats.length }) }}
              </p>
              <button
                @click="mobileCatOpen = true"
                class="lg:hidden flex items-center gap-1.5 px-3 py-2 rounded-lg bg-primary/10 text-primary border border-primary/20 text-xs font-semibold hover:bg-primary/15 transition-all"
              >
                <Layers class="w-3.5 h-3.5" />
                {{ $t('browse.categories') }}
              </button>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-20">
              <Loader2 class="w-8 h-8 animate-spin text-primary" />
            </div>

            <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
              <button
                v-for="cat in sidebarCats"
                :key="cat.id"
                @click="navigateTo(cat.id)"
                class="reveal group relative rounded-2xl overflow-hidden transition-all duration-300 hover:scale-[1.02] hover:shadow-xl text-right aspect-[4/3] sm:aspect-[16/10] border border-white/5"
                :class="`reveal-delay-${(cat.id % 4)}`"
              >
                <!-- Background image -->
                <img v-if="cat.image" :src="'/' + cat.image" :alt="cat.name"
                  class="absolute inset-0 w-full h-full object-cover transition-all duration-500 group-hover:scale-105"
                  @error="($event.target as HTMLImageElement).style.display = 'none'" />

                <!-- Gradient fallback when no image -->
                <div v-show="!cat.image" class="absolute inset-0"
                  :style="{
                    background: `linear-gradient(135deg, ${getCatStyle(cat).from}, ${getCatStyle(cat).to})`,
                  }" />

                <!-- Overlay gradient for readability -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-black/10 group-hover:to-black/20 transition-all duration-300" />

                <!-- Content -->
                <div class="relative h-full flex flex-col justify-between p-4 sm:p-5">
                  <!-- Top: icon -->
                  <div class="flex items-start">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl overflow-hidden border-2 border-white/20 backdrop-blur-sm shadow-lg"
                      :style="{
                        background: `linear-gradient(135deg, ${getCatStyle(cat).from}, ${getCatStyle(cat).to})`,
                      }">
                      <component :is="getCatStyle(cat).icon" class="w-full h-full p-2.5 text-white" />
                    </div>
                  </div>

                  <!-- Bottom: info -->
                  <div class="flex items-end justify-between gap-2">
                    <div class="min-w-0">
                      <h3 class="text-sm sm:text-base font-bold text-white drop-shadow-lg">
                        {{ cat.name }}
                      </h3>
                      <p class="text-[11px] sm:text-xs text-white/70 drop-shadow">
                        {{ cat.products_count }} {{ cat.products_count !== 1 ? $t('common.products') : $t('common.product') }}
                      </p>
                    </div>
                    <div class="shrink-0 w-8 h-8 rounded-lg bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center transition-all duration-300 group-hover:bg-primary/20 group-hover:border-primary/40">
                      <ChevronLeft class="w-4 h-4 text-white/80 group-hover:text-primary transition-colors" />
                    </div>
                  </div>
                </div>
              </button>
            </div>
          </main>
        </div>
      </template>

      <!-- ===== PRODUCTS VIEW (leaf categories only) ===== -->
      <template v-else>
        <div class="flex items-center gap-3 mb-5">
          <button @click="goHome" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white/[0.03] border border-white/5 text-xs text-muted-foreground hover:text-white hover:bg-white/[0.06] transition-all lg:hidden">
            <ChevronRight class="w-3.5 h-3.5" /> {{ $t('common.back') }}
          </button>
        </div>

        <div class="grid lg:grid-cols-[280px_1fr] gap-8">
          <!-- Sidebar -->
          <aside class="hidden lg:block">
            <div class="sticky top-28 space-y-6">
              <button @click="goHome" class="w-full flex items-center gap-2 px-4 py-3 rounded-xl bg-white/[0.02] border border-white/5 text-sm text-muted-foreground hover:text-white hover:bg-white/[0.04] hover:border-primary/20 transition-all backdrop-blur-xl">
                <ChevronRight class="w-4 h-4 text-primary" />
                {{ $t('nav.allCategories') }}
              </button>

              <div v-if="currentCat" class="bg-gradient-to-br from-primary/[0.06] to-transparent border border-primary/15 rounded-2xl p-5 backdrop-blur-xl">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-lg bg-primary/10 border border-primary/20 flex items-center justify-center">
                    <Tag class="w-4 h-4 text-primary" />
                  </div>
                  <div>
                    <h3 class="text-xs font-mono text-primary tracking-wider uppercase">{{ $t('browse.currentCategory') }}</h3>
                    <p class="text-base font-bold text-white mt-0.5">{{ currentCat.name }}</p>
                  </div>
                </div>
              </div>

              <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-5 backdrop-blur-xl">
                <div class="flex items-center gap-2 mb-4">
                  <SlidersHorizontal class="w-4 h-4 text-primary" />
                  <h3 class="text-sm font-semibold text-white tracking-wide">{{ $t('browse.maxPrice') }}</h3>
                </div>
                <input type="range" v-model="priceRange" min="0" max="100" class="w-full h-1.5 bg-white/10 rounded-full appearance-none cursor-pointer accent-primary" />
                <div class="flex justify-between mt-3 text-xs font-mono">
                  <span class="text-muted-foreground">$0</span>
                  <span class="text-primary font-bold">${{ priceRange }}</span>
                </div>
              </div>

              <div class="bg-gradient-to-br from-primary/[0.05] to-transparent border border-primary/10 rounded-2xl p-5">
                <div class="flex items-center gap-3 mb-4">
                  <div class="w-10 h-10 rounded-lg bg-primary/10 border border-primary/20 flex items-center justify-center">
                    <Zap class="w-5 h-5 text-primary" />
                  </div>
                  <div>
                    <h4 class="text-sm font-bold text-white">{{ $t('product.autoDelivery') }}</h4>
                    <p class="text-[10px] text-muted-foreground font-mono">AUTOMATED SYSTEM</p>
                  </div>
                </div>
                <p class="text-xs text-muted-foreground leading-relaxed">
                  {{ $t('product.autoDeliveryDesc') }}
                </p>
              </div>
            </div>
          </aside>

          <!-- Products -->
          <main class="min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6 p-3 sm:p-4 bg-white/[0.02] border border-white/5 rounded-2xl backdrop-blur-xl">
              <div class="flex items-center gap-3">
                <span class="text-xs sm:text-sm text-muted-foreground whitespace-nowrap">
                  <span class="font-mono text-white font-bold">{{ products.length }}</span> {{ products.length !== 1 ? $t('common.products') : $t('common.product') }}
                </span>
              </div>
              <div class="flex items-center gap-2">
                <div class="relative w-48 sm:w-56">
                  <input v-model="searchQuery" type="text" :placeholder="$t('browse.searchProducts')"
                    class="w-full bg-white/[0.02] border border-white/10 rounded-lg py-2 px-3 pr-8 text-xs text-white placeholder:text-muted-foreground/50 focus:outline-none focus:border-primary/30 transition-all font-sans" />
                  <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-muted-foreground/50" />
                </div>
                <select v-model="sortBy"
                  class="bg-white/[0.02] border border-white/10 rounded-lg py-2 px-2.5 text-[11px] text-white focus:outline-none focus:border-primary/30 cursor-pointer">
                  <option value="popular" class="bg-card">{{ $t('browse.sortPopular') }}</option>
                  <option value="price_asc" class="bg-card">{{ $t('browse.sortPriceLow') }}</option>
                  <option value="price_desc" class="bg-card">{{ $t('browse.sortPriceHigh') }}</option>
                </select>
                <div class="hidden sm:flex items-center gap-1 p-1 bg-white/[0.02] border border-white/10 rounded-lg">
                  <button @click="viewMode = 'grid'" class="p-1.5 rounded transition-colors" :class="viewMode === 'grid' ? 'bg-primary/20 text-primary' : 'text-muted-foreground hover:text-white'">
                    <Grid2x2 class="w-4 h-4" />
                  </button>
                  <button @click="viewMode = 'list'" class="p-1.5 rounded transition-colors" :class="viewMode === 'list' ? 'bg-primary/20 text-primary' : 'text-muted-foreground hover:text-white'">
                    <List class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>

            <div v-if="productLoading" class="flex items-center justify-center py-20">
              <Loader2 class="w-8 h-8 animate-spin text-primary" />
            </div>

            <template v-else-if="filteredProducts.length > 0">
              <div class="grid grid-cols-2 md:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-5">
                <Link v-for="(product, i) in filteredProducts" :key="product.id" :href="`/products/${product.id}`"
                  class="reveal group relative bg-white/[0.02] border border-white/5 rounded-xl sm:rounded-2xl overflow-hidden transition-all duration-300 hover:border-primary/30 hover:bg-white/[0.04]"
                  :class="`reveal-delay-${i % 4}`">
                  <div class="aspect-[16/10] overflow-hidden relative bg-card">
                    <img :src="product.image || '/placeholder-product.png'" :alt="product.name"
                      class="w-full h-full object-cover transition-all duration-700 group-hover:scale-105 opacity-80 group-hover:opacity-100" loading="lazy"
                      @error="($event.target as HTMLImageElement).src = '/placeholder-product.png'" />
                    <div class="absolute inset-0 bg-gradient-to-t from-background via-background/40 to-transparent" />
                    <div class="absolute top-2 sm:top-3 right-2 sm:right-3 flex flex-col gap-2 items-start">
                      <span v-if="product.is_auto" class="flex items-center gap-1 px-1.5 sm:px-2 py-0.5 sm:py-1 text-[9px] sm:text-[10px] font-mono font-semibold uppercase tracking-wider bg-success/15 text-success border border-success/20 rounded backdrop-blur-sm">
                        <Zap class="w-2 h-2 sm:w-2.5 sm:h-2.5" /> {{ $t('browse.instant') }}
                      </span>
                    </div>
                    <div class="absolute top-2 sm:top-3 left-2 sm:left-3 w-2 sm:w-3 h-2 sm:h-3 border-t border-l border-primary/20 group-hover:border-primary/50 transition-colors"></div>
                    <div class="absolute bottom-2 sm:bottom-3 right-2 sm:right-3 w-2 sm:w-3 h-2 sm:h-3 border-b border-r border-primary/20 group-hover:border-primary/50 transition-colors"></div>
                  </div>
                  <div class="p-3 sm:p-5">
                    <div class="flex items-center gap-2 mb-1 sm:mb-2">
                      <span class="text-[9px] sm:text-[10px] font-mono text-muted-foreground uppercase tracking-wider truncate max-w-full">{{ product.category?.name || '' }}</span>
                    </div>
                    <h3 class="text-xs sm:text-sm md:text-base font-semibold text-white group-hover:text-primary transition-colors mb-2 sm:mb-4 leading-tight line-clamp-2 min-h-[2rem] sm:min-h-[3rem]">
                      {{ product.name }}
                    </h3>
                    <div class="flex items-end justify-between gap-2">
                      <div>
                        <span class="text-sm sm:text-lg md:text-xl font-bold text-white font-mono tracking-tight">${{ Number(product.price).toFixed(2) }}</span>
                      </div>
                      <div class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1.5 sm:py-2 text-[10px] sm:text-xs font-semibold bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-all active:scale-95 glow-primary shrink-0">
                        <ShoppingCart class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        <span class="hidden sm:inline">{{ $t('browse.buy') }}</span>
                      </div>
                    </div>
                  </div>
                </Link>
              </div>

              <div v-if="pagination && pagination.last_page > 1" class="flex items-center justify-center gap-1.5 sm:gap-2 mt-8 sm:mt-12">
                <button :disabled="page <= 1" @click="goToPage(page - 1)"
                  class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg border border-white/5 text-muted-foreground hover:bg-white/5 hover:text-white transition-colors disabled:opacity-30">
                  <ChevronLeft class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                </button>
                <button v-for="p in pagination.last_page" :key="p" @click="goToPage(p)"
                  class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg font-mono text-xs sm:text-sm transition-colors"
                  :class="p === page ? 'bg-primary text-primary-foreground' : 'border border-white/5 text-muted-foreground hover:bg-white/5 hover:text-white'">{{ p }}</button>
                <button :disabled="page >= pagination.last_page" @click="goToPage(page + 1)"
                  class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg border border-white/5 text-muted-foreground hover:bg-white/5 hover:text-white transition-colors disabled:opacity-30">
                  <ChevronLeft class="w-3.5 h-3.5 sm:w-4 sm:h-4 rotate-180" />
                </button>
              </div>
            </template>

            <div v-else class="flex flex-col items-center justify-center py-16 sm:py-20 bg-white/[0.02] border border-white/5 rounded-2xl px-4">
              <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-white/[0.02] border border-white/5 flex items-center justify-center mb-4">
                <Package class="w-6 h-6 sm:w-8 sm:h-8 text-muted-foreground/50" />
              </div>
              <h3 class="text-base sm:text-lg font-semibold text-white mb-2">{{ $t('common.noResults') }}</h3>
              <p class="text-xs sm:text-sm text-muted-foreground mb-6 text-center">{{ $t('common.noResults') }}</p>
              <button @click="clearFilters" class="px-5 py-2 text-sm font-semibold bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-all">
                {{ $t('common.clearFilters') }}
              </button>
            </div>
          </main>
        </div>
      </template>
    </div>

    <!-- Mobile Drawer -->
    <Transition name="drawer">
      <div v-if="mobileCatOpen" class="fixed inset-0 z-[100] lg:hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="mobileCatOpen = false" />
        <div class="absolute inset-y-0 left-0 w-[85vw] max-w-sm bg-background border-l border-white/5 shadow-2xl overflow-y-auto">
          <div class="p-4 sm:p-5">
            <div class="flex items-center justify-between mb-5">
              <h3 class="text-sm font-bold text-white">{{ isRoot ? $t('browse.categories') : $t('browse.subcategories') }}</h3>
              <button @click="mobileCatOpen = false" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white/[0.03] border border-white/5 text-muted-foreground hover:text-white transition-colors">
                <X class="w-4 h-4" />
              </button>
            </div>
            <button v-if="!isRoot" @click="goHome"
              class="w-full flex items-center gap-2 px-4 py-3 rounded-xl bg-white/[0.02] border border-white/5 text-sm text-muted-foreground hover:text-white hover:bg-white/[0.04] hover:border-primary/20 transition-all mb-4">
              <ChevronRight class="w-4 h-4 text-primary" />
              {{ $t('nav.allCategories') }}
            </button>
            <div class="flex flex-col gap-1.5">
              <button v-for="cat in sidebarCats" :key="cat.id" @click="navigateTo(cat.id)"
                class="group flex items-center justify-between px-4 py-3.5 rounded-xl text-sm font-semibold transition-all duration-200 text-right w-full border"
                :class="cat.id === props.categoryId ? 'bg-primary/10 text-primary border-primary/25' : 'text-muted-foreground hover:text-white hover:bg-white/[0.04] border-transparent hover:border-white/5'">
                <div class="flex items-center gap-3 min-w-0">
                  <div class="w-10 h-10 rounded-xl overflow-hidden shrink-0 border"
                    :style="{
                      background: `linear-gradient(135deg, ${getCatStyle(cat).from}, ${getCatStyle(cat).to})`,
                      borderColor: getCatStyle(cat).border,
                    }">
                    <img v-if="cat.image" :src="'/' + cat.image" :alt="cat.name"
                      class="w-full h-full object-cover"
                      @error="($event.target as HTMLImageElement).style.display = 'none'" />
                    <div v-show="!cat.image" class="w-full h-full flex items-center justify-center">
                      <component :is="getCatStyle(cat).icon" class="w-5 h-5 text-white/90" />
                    </div>
                  </div>
                  <span class="text-sm font-semibold truncate">{{ cat.name }}</span>
                </div>
                <span class="text-[11px] font-mono text-muted-foreground/60 shrink-0">{{ cat.products_count }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Footer -->
    <AppFooter />
  </div>
</template>

<style scoped>
.reveal {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.reveal.revealed {
  opacity: 1;
  transform: translateY(0);
}
.reveal-delay-0 { transition-delay: 0s; }
.reveal-delay-1 { transition-delay: 0.1s; }
.reveal-delay-2 { transition-delay: 0.2s; }
.reveal-delay-3 { transition-delay: 0.3s; }

select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: left 0.75rem center;
  padding-left: 2.5rem;
}

input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 16px;
  height: 16px;
  background: hsl(186 100% 55%);
  border-radius: 50%;
  cursor: pointer;
  box-shadow: 0 0 10px hsl(186 100% 55% / 0.5);
}

.drawer-enter-active, .drawer-leave-active { transition: opacity 0.25s ease; }
.drawer-enter-active > div:last-child, .drawer-leave-active > div:last-child { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.drawer-enter-from, .drawer-leave-to { opacity: 0; }
.drawer-enter-from > div:last-child, .drawer-leave-to > div:last-child { transform: translateX(-100%); }
</style>
