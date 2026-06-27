<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue';
import { onClickOutside } from '@vueuse/core';
import { useApi } from '@/composables/useApi';
import { useAuthStore } from '@/stores/authStore';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import { logout } from '@/routes';
import { 
  Gamepad2, Zap, Shield, Clock, ChevronLeft, ChevronRight,
  ShoppingCart, CreditCard, Gift, Download, Sparkles,
  TrendingUp, Users, Package, ArrowLeft, Hexagon, Cpu,
  User, LogOut, Settings, ChevronDown,
} from 'lucide-vue-next';

// ── Reactive State ──
const isScrolled = ref(false);
const mobileMenuOpen = ref(false);

const handleScroll = () => {
  isScrolled.value = window.scrollY > 20;
};

let revealObserver: IntersectionObserver | null = null;

const initReveal = () => {
  revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          revealObserver?.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.08, rootMargin: '0px 0px -40px 0px' }
  );
  document.querySelectorAll('.reveal').forEach((el) => revealObserver?.observe(el));
};

const initCounters = () => {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const el = entry.target as HTMLElement;
          const target = parseInt(el.dataset.target || '0');
          let current = 0;
          const step = Math.max(1, Math.floor(target / 50));
          const interval = setInterval(() => {
            current += step;
            if (current >= target) {
              current = target;
              clearInterval(interval);
            }
            el.textContent = current.toLocaleString('ar-SA') + (el.dataset.suffix || '');
          }, 30);
          observer.unobserve(el);
        }
      });
    },
    { threshold: 0.5 }
  );
  document.querySelectorAll('.counter-anim').forEach((el) => observer.observe(el));
};

const initMagnetic = () => {
  document.querySelectorAll('.magnetic').forEach((el) => {
    const htmlEl = el as HTMLElement;
    htmlEl.addEventListener('mousemove', (e: MouseEvent) => {
      const rect = htmlEl.getBoundingClientRect();
      const x = (e.clientX - rect.left - rect.width / 2) * 0.2;
      const y = (e.clientY - rect.top - rect.height / 2) * 0.2;
      htmlEl.style.transform = `translate(${x}px, ${y}px)`;
    });
    htmlEl.addEventListener('mouseleave', () => {
      htmlEl.style.transform = 'translate(0,0)';
    });
  });
};

const initTilt = () => {
  document.querySelectorAll('.tilt-card').forEach((el) => {
    const htmlEl = el as HTMLElement;
    htmlEl.addEventListener('mousemove', (e: MouseEvent) => {
      const rect = htmlEl.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width - 0.5;
      const y = (e.clientY - rect.top) / rect.height - 0.5;
      htmlEl.style.transform = `perspective(800px) rotateY(${x * 6}deg) rotateX(${-y * 6}deg) scale(1.01)`;
      htmlEl.style.boxShadow = `${-x * 15}px ${y * 15}px 40px hsl(186 100% 55% / 0.06)`;
    });
    htmlEl.addEventListener('mouseleave', () => {
      htmlEl.style.transform = 'perspective(800px) rotateY(0) rotateX(0) scale(1)';
      htmlEl.style.boxShadow = 'none';
    });
  });
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true });
  initReveal();
  initCounters();
  initMagnetic();
  initTilt();
  fetchFeatured();
  fetchCategories();
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  revealObserver?.disconnect();
});

// ── API & Auth ──
const api = useApi();
const authStore = useAuthStore();
const toast = useToast();

const user = computed(() => authStore.user);
const userMenuOpen = ref(false);
const userMenuRef = ref<HTMLElement | null>(null);
onClickOutside(userMenuRef, () => { userMenuOpen.value = false; });

function handleLogout() {
  authStore.logout();
  router.post(logout().url);
}

function fmtPrice(v: string | number | null | undefined): string {
  if (v == null) return '0.00';
  const n = typeof v === 'string' ? parseFloat(v) : v;
  return isNaN(n) ? '0.00' : n.toFixed(2);
}

// ── Data ──
const featuredProducts = ref<any[]>([]);
const categoriesList = ref<any[]>([]);
const isLoading = ref(false);

const firstRow = computed(() => featuredProducts.value.slice(0, 3));
const secondRow = computed(() => featuredProducts.value.slice(3, 5));

const categoryColors = [
  'from-cyan-500/20 to-cyan-500/5',
  'from-purple-500/20 to-purple-500/5',
  'from-emerald-500/20 to-emerald-500/5',
  'from-amber-500/20 to-amber-500/5',
  'from-rose-500/20 to-rose-500/5',
  'from-blue-500/20 to-blue-500/5',
];
const categoryIconMap: Record<string, any> = {
  'Brawl Stars': Gamepad2, 'Gaming': Gamepad2, 'ألعاب': Gamepad2,
  'Streaming': Sparkles, 'بث': Sparkles, 'Music': Download, 'موسيقى': Download,
  'Software': Gift, 'برامج': Gift, 'Movies': Gift, 'أفلام': Gift,
  'Gift Cards': Gift, 'بطاقات هدايا': Gift,
};

async function fetchFeatured() {
  try {
    const res = await api.get('/products', { params: { per_page: 20, categories: 'Brawl Stars,Playstation USA,pubg mobile الي,freefire' } });
    const d = res.data;
    const products = Array.isArray(d.data) ? d.data : [];
    const seen = new Set<string>();
    const deduped: any[] = [];
    for (const p of products) {
      const catName = p.category?.name;
      if (catName && !seen.has(catName)) { seen.add(catName); deduped.push(p); }
      if (deduped.length === 4) break;
    }
    if (deduped.length < 5) {
      const fallback = await api.get('/products', { params: { per_page: 1, page: Math.floor(Math.random() * 10) + 1 } });
      const fbData = fallback.data?.data;
      if (Array.isArray(fbData) && fbData.length) {
        deduped.push(fbData[0]);
      }
    }
    featuredProducts.value = deduped;
    nextTick(() => { initReveal(); initTilt(); });
  } catch (err: any) {
    console.error('fetch featured failed', parseApiError(err));
  }
}

async function fetchCategories() {
  try {
    const res = await api.get('/categories', { params: { per_page: 3 } });
    const cats = Array.isArray(res.data.data) ? res.data.data : Array.isArray(res.data) ? res.data : [];
    categoriesList.value = cats.map((cat: any, i: number) => ({
      name: cat.name,
      icon: categoryIconMap[cat.name] || Gamepad2,
      count: (cat.products_count || '0') + '+',
      color: categoryColors[i % categoryColors.length],
    }));
    nextTick(initReveal);
  } catch (e) {
    console.error('fetch categories failed', e);
  }
}

const steps = [
  { num: '01', title: 'اختر', desc: 'تصفح الكتالوج واختر بطاقتك المفضلة', icon: ShoppingCart, color: 'text-cyan-400', border: 'border-cyan-500/20', bg: 'bg-cyan-500/5' },
  { num: '02', title: 'ادفع', desc: 'دفع آمن باستخدام رصيدك أو إيداع جديد', icon: CreditCard, color: 'text-purple-400', border: 'border-purple-500/20', bg: 'bg-purple-500/5' },
  { num: '03', title: 'شحن فوري', desc: 'يتم شحن اللعبة تلقائياً — لا أكواد، لا تعقيد', icon: Zap, color: 'text-emerald-400', border: 'border-emerald-500/20', bg: 'bg-emerald-500/5' },
  { num: '04', title: 'العب', desc: 'انطلق في اللعب مباشرة بدون أي خطوات إضافية', icon: Gamepad2, color: 'text-amber-400', border: 'border-amber-500/20', bg: 'bg-amber-500/5' },
];

const stats = [
  { label: 'طلبات منجزة', value: '50000', suffix: '+', icon: Package, sparkline: 'M0,20 L15,18 L30,15 L45,16 L60,10 L75,12 L90,6 L105,8 L120,2' },
  { label: 'لاعب سعيد', value: '12000', suffix: '+', icon: Users, sparkline: 'M0,18 L20,15 L40,12 L60,14 L80,8 L100,10 L120,4' },
  { label: 'استقرار النظام', value: '99', suffix: '.9%', icon: Shield, sparkline: 'M0,4 L20,3 L40,5 L60,2 L80,3 L100,2 L120,1' },
  { label: 'متوسط التوصيل', value: '3', suffix: 'ث', icon: Clock, sparkline: 'M0,18 L15,14 L30,16 L45,10 L60,12 L75,8 L90,6 L105,5 L120,4' },
];
</script>

<template>
  <Head title="مرحباً">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Orbitron:wght@500;600;700;800&display=swap" rel="stylesheet" />
  </Head>

  <div dir="rtl" class="min-h-screen bg-background text-foreground relative overflow-x-hidden" style="font-family: 'Cairo', 'Inter', system-ui, sans-serif;">
    <!-- ═══ Background Grid ═══ -->
    <div class="fixed inset-0 bg-grid pointer-events-none z-0" />
    <div class="fixed inset-0 pointer-events-none z-0" style="background: radial-gradient(ellipse at 50% 0%, hsl(186 100% 55% / 0.04) 0%, transparent 60%)" />

    <!-- ═══ NAVIGATION ═══ -->
    <nav
      class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
      :class="isScrolled ? 'glass-strong border-b border-primary/10' : 'bg-transparent'"
    >
      <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        <!-- Logo -->
        <Link href="/" class="flex items-center gap-2.5 group">
          <div class="w-8 h-8 rounded-md flex items-center justify-center border border-primary/30 bg-primary/10 glow-primary">
            <Cpu class="w-4 h-4 text-primary" />
          </div>
          <span class="font-display text-lg font-bold tracking-wider text-white group-hover:text-primary transition-colors" style="font-family: 'Orbitron', 'Cairo', sans-serif;">
            CoreS
          </span>
        </Link>

        <!-- Desktop Nav -->
        <div class="hidden md:flex items-center gap-8">
          <a href="#products" class="text-sm text-muted-foreground hover:text-primary transition-colors magnetic">المنتجات</a>
          <a href="#categories" class="text-sm text-muted-foreground hover:text-primary transition-colors magnetic">الفئات</a>
          <a href="#how" class="text-sm text-muted-foreground hover:text-primary transition-colors magnetic">كيف يعمل</a>
          <!-- Auth user dropdown -->
          <div v-if="user" ref="userMenuRef" class="relative">
            <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 px-3 py-2 text-sm font-medium bg-primary/5 text-primary border border-primary/20 rounded-md hover:bg-primary/10 hover:border-primary/40 transition-all">
              <div class="w-6 h-6 rounded-full bg-primary/20 border border-primary/30 flex items-center justify-center">
                <span class="text-[10px] font-bold text-primary">{{ user.name.charAt(0) }}</span>
              </div>
              <span class="text-xs text-white/80">{{ user.name }}</span>
              <ChevronDown class="w-3 h-3 text-primary/60 transition-transform duration-200" :class="userMenuOpen ? 'rotate-180' : ''" />
            </button>
            <div v-if="userMenuOpen" class="absolute left-0 top-full mt-2 w-48 bg-card border border-primary/10 rounded-lg shadow-2xl shadow-black/50 backdrop-blur-xl overflow-hidden z-50">
              <div class="p-1.5 flex flex-col gap-0.5">
                <Link href="/settings/profile" class="flex items-center gap-3 px-3 py-2 text-sm text-muted-foreground hover:text-white hover:bg-primary/5 rounded-md transition-colors" @click="userMenuOpen = false">
                  <User class="w-4 h-4 text-primary/70" /> الملف الشخصي
                </Link>
                <Link href="/settings/appearance" class="flex items-center gap-3 px-3 py-2 text-sm text-muted-foreground hover:text-white hover:bg-primary/5 rounded-md transition-colors" @click="userMenuOpen = false">
                  <Settings class="w-4 h-4 text-primary/70" /> الإعدادات
                </Link>
                <div class="h-px bg-primary/10 my-1" />
                <Link :href="logout()" as="button" class="flex items-center gap-3 px-3 py-2 text-sm text-red-400/70 hover:text-red-400 hover:bg-red-500/5 rounded-md transition-colors w-full text-right" @click="userMenuOpen = false">
                  <LogOut class="w-4 h-4" /> تسجيل الخروج
                </Link>
              </div>
            </div>
          </div>
          <!-- Guest: login/register -->
          <Link v-if="!user" href="/login" class="text-sm text-muted-foreground hover:text-primary transition-colors magnetic">تسجيل الدخول</Link>
          <Link v-if="!user" href="/register" class="magnetic px-5 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-md hover:bg-primary/90 transition-all glow-primary">
            ابدأ الآن
          </Link>
        </div>

        <!-- Mobile Toggle -->
        <button
          class="md:hidden w-10 h-10 flex items-center justify-center text-white"
          @click="mobileMenuOpen = !mobileMenuOpen"
          aria-label="القائمة"
        >
          <component :is="mobileMenuOpen ? ChevronRight : ChevronLeft" class="w-5 h-5" />
        </button>
      </div>

      <!-- Mobile Menu -->
      <div
        v-if="mobileMenuOpen"
        class="md:hidden glass-strong border-t border-primary/10"
      >
        <div class="px-6 py-4 flex flex-col gap-3">
          <a href="#products" class="text-sm text-muted-foreground hover:text-primary py-2" @click="mobileMenuOpen = false">المنتجات</a>
          <a href="#categories" class="text-sm text-muted-foreground hover:text-primary py-2" @click="mobileMenuOpen = false">الفئات</a>
          <a href="#how" class="text-sm text-muted-foreground hover:text-primary py-2" @click="mobileMenuOpen = false">كيف يعمل</a>
          <!-- Auth'd: profile/settings/logout -->
          <template v-if="user">
            <div class="h-px bg-primary/10 my-1" />
            <Link href="/settings/profile" class="flex items-center gap-3 text-sm text-muted-foreground hover:text-primary py-2" @click="mobileMenuOpen = false">
              <User class="w-4 h-4 text-primary/70" /> الملف الشخصي
            </Link>
            <Link href="/settings/appearance" class="flex items-center gap-3 text-sm text-muted-foreground hover:text-primary py-2" @click="mobileMenuOpen = false">
              <Settings class="w-4 h-4 text-primary/70" /> الإعدادات
            </Link>
            <Link :href="logout()" as="button" class="flex items-center gap-3 text-sm text-red-400/70 hover:text-red-400 py-2" @click="mobileMenuOpen = false">
              <LogOut class="w-4 h-4" /> تسجيل الخروج
            </Link>
          </template>
          <!-- Guest: login/register -->
          <template v-if="!user">
            <Link href="/login" class="text-sm text-muted-foreground hover:text-primary py-2">تسجيل الدخول</Link>
            <Link href="/register" class="px-5 py-2.5 text-sm font-medium bg-primary text-primary-foreground rounded-md text-center glow-primary">
              ابدأ الآن
            </Link>
          </template>
        </div>
      </div>
    </nav>

    <!-- ═══ HERO ═══ -->
    <section class="relative min-h-screen flex items-center pt-16 pb-20">
      <div class="max-w-7xl mx-auto px-6 w-full">
        <div class="grid lg:grid-cols-12 gap-12 items-center">
          <!-- Right Content (RTL: content on right) -->
          <div class="lg:col-span-7 space-y-8">
            <!-- Status Badge -->
            <div class="reveal inline-flex items-center gap-2.5 px-4 py-2 rounded-full border border-success/20 bg-success/5">
              <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75" />
                <span class="relative inline-flex rounded-full h-2 w-2 bg-success" />
              </span>
              <span class="text-xs font-mono text-success tracking-wider uppercase">النظام متصل — توصيل فوري</span>
            </div>

            <!-- Headline -->
            <h1 class="reveal reveal-delay-1 text-4xl md:text-5xl lg:text-6xl font-bold leading-[1.15] tracking-tight" style="font-family: 'Cairo', 'Orbitron', sans-serif;">
              <span class="block text-white">بطاقات رقمية</span>
              <span class="block text-gradient mt-2">توصيل فوري</span>
              <span class="block text-white/60 mt-2 text-3xl md:text-4xl lg:text-5xl font-light">بدون انتظار</span>
            </h1>

            <!-- Description -->
            <p class="reveal reveal-delay-2 text-base md:text-lg text-muted-foreground max-w-xl leading-relaxed">
              وجهتك الأولى لبطاقات الألعاب والاشتراكات الرقمية. شحن تلقائي في ثوانٍ.
              آمن. سريع. مُبنى للاعبين.
            </p>

            <!-- CTAs -->
            <div class="reveal reveal-delay-3 flex flex-wrap gap-4">
              <a href="#products" class="magnetic inline-flex items-center gap-2 px-8 py-3.5 bg-primary text-primary-foreground font-semibold rounded-md hover:bg-primary/90 transition-all glow-primary">
                <ShoppingCart class="w-4 h-4" />
                تصفح المنتجات
              </a>
              <Link href="/register" class="magnetic inline-flex items-center gap-2 px-8 py-3.5 border border-primary/30 text-primary font-medium rounded-md hover:bg-primary/5 hover:border-primary/50 transition-all">
                <Sparkles class="w-4 h-4" />
                إنشاء حساب
              </Link>
            </div>

            <!-- Mini Stats -->
            <div class="reveal reveal-delay-4 flex items-center gap-6 pt-4">
              <div class="flex items-center gap-2">
                <Shield class="w-4 h-4 text-success" />
                <span class="text-xs text-muted-foreground">SSL مشفر</span>
              </div>
              <div class="w-px h-4 bg-border" />
              <div class="flex items-center gap-2">
                <Zap class="w-4 h-4 text-warning" />
                <span class="text-xs text-muted-foreground">شحن تلقائي</span>
              </div>
              <div class="w-px h-4 bg-border" />
              <div class="flex items-center gap-2">
                <Clock class="w-4 h-4 text-primary" />
                <span class="text-xs text-muted-foreground">دعم 24/7</span>
              </div>
            </div>
          </div>

          <!-- Left: Terminal Card (RTL: terminal on left) -->
          <div class="lg:col-span-5 reveal reveal-delay-2">
            <div class="glass border border-primary/10 rounded-xl overflow-hidden relative">
              <!-- Terminal Bar -->
              <div class="flex items-center gap-2 px-4 py-3 border-b border-primary/10 bg-primary/5">
                <div class="w-3 h-3 rounded-full bg-danger/80" />
                <div class="w-3 h-3 rounded-full bg-warning/80" />
                <div class="w-3 h-3 rounded-full bg-success/80" />
                <span class="mr-auto text-[10px] font-mono text-muted-foreground">cores://terminal</span>
              </div>
              <!-- Terminal Body -->
              <div class="p-5 font-mono text-xs space-y-3">
                <div class="flex items-center gap-2">
                  <span class="text-primary">&gt;</span>
                  <span class="text-muted-foreground">system.status</span>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-muted-foreground w-20 text-right">الاستقرار</span>
                  <div class="flex-1 h-1.5 bg-muted rounded-full overflow-hidden">
                    <div class="h-full w-[99.9%] rounded-full bg-gradient-to-r from-primary to-success" />
                  </div>
                  <span class="text-success text-[10px]">99.9%</span>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-muted-foreground w-20 text-right">التوصيل</span>
                  <div class="flex-1 h-1.5 bg-muted rounded-full overflow-hidden">
                    <div class="h-full w-[98%] rounded-full bg-primary" />
                  </div>
                  <span class="text-primary text-[10px]">3.2ث</span>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-muted-foreground w-20 text-right">المخزون</span>
                  <div class="flex-1 h-1.5 bg-muted rounded-full overflow-hidden">
                    <div class="h-full w-[85%] rounded-full bg-gradient-to-r from-purple-400 to-primary" />
                  </div>
                  <span class="text-purple-400 text-[10px]">85%</span>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-muted-foreground w-20 text-right">الأمان</span>
                  <div class="flex-1 h-1.5 bg-muted rounded-full overflow-hidden">
                    <div class="h-full w-full rounded-full bg-success" />
                  </div>
                  <span class="text-success text-[10px]">PASS</span>
                </div>
                <div class="pt-3 border-t border-primary/10">
                  <div class="flex items-center gap-2">
                    <span class="text-primary">&gt;</span>
                    <span class="text-muted-foreground">order.place —product steam_50</span>
                  </div>
                  <div class="mt-1.5 pr-4 text-[10px]">
                    <span class="text-success">✓</span> <span class="text-muted-foreground">تم تأكيد الطلب</span>
                  </div>
                  <div class="pr-4 text-[10px]">
                    <span class="text-success">✓</span> <span class="text-muted-foreground">تمت معالجة الدفع</span>
                  </div>
                  <div class="pr-4 text-[10px]">
                    <span class="text-success">✓</span> <span class="text-muted-foreground">تم الشحن التلقائي على الحساب</span>
                  </div>
                  <div class="mt-2 pr-4">
                    <span class="text-primary">&gt;</span> <span class="cursor-blink" />
                  </div>
                </div>
              </div>
              <!-- Sparkline -->
              <div class="px-5 pb-4 pt-1">
                <svg class="w-full" height="32" viewBox="0 0 300 32" preserveAspectRatio="none">
                  <path
                    d="M0,28 L20,24 L45,26 L70,18 L95,20 L120,12 L145,15 L170,8 L195,10 L220,4 L245,6 L270,2 L300,1"
                    fill="none"
                    stroke="hsl(186 100% 55%)"
                    stroke-width="1.5"
                    opacity="0.5"
                  />
                  <path
                    d="M0,28 L20,24 L45,26 L70,18 L95,20 L120,12 L145,15 L170,8 L195,10 L220,4 L245,6 L270,2 L300,1 L300,32 L0,32 Z"
                    fill="url(#heroGradAr)"
                    opacity="0.1"
                  />
                  <defs>
                    <linearGradient id="heroGradAr" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="hsl(186 100% 55%)" />
                      <stop offset="100%" stop-color="transparent" />
                    </linearGradient>
                  </defs>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ STATS BAR ═══ -->
    <section class="py-10 border-y border-primary/5 bg-primary/[0.02]">
      <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
          <div v-for="(stat, i) in stats" :key="i" class="reveal glass border border-primary/5 rounded-lg p-5 relative overflow-hidden" :class="`reveal-delay-${i}`">
            <div class="absolute top-0 left-0 w-16 h-16 bg-gradient-to-br from-primary/5 to-transparent pointer-events-none" />
            <div class="flex items-center justify-between mb-3">
              <component :is="stat.icon" class="w-4 h-4 text-primary/60" />
              <svg class="w-16 h-6" viewBox="0 0 120 24" preserveAspectRatio="none">
                <path :d="stat.sparkline" fill="none" stroke="hsl(186 100% 55%)" stroke-width="1.5" opacity="0.4" />
              </svg>
            </div>
            <div class="font-mono text-2xl md:text-3xl font-bold text-white counter-anim" :data-target="stat.value" :data-suffix="stat.suffix">
              0{{ stat.suffix }}
            </div>
            <div class="text-[10px] font-mono text-muted-foreground tracking-wider uppercase mt-1">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ FEATURED PRODUCTS ═══ -->
    <section id="products" class="py-24 md:py-32">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
          <div>
            <span class="section-label reveal">مميز</span>
            <h2 class="text-3xl md:text-4xl font-bold tracking-tight mt-3 reveal reveal-delay-1" style="font-family: 'Cairo', 'Orbitron', sans-serif;">
              الأكثر طلباً <span class="text-gradient">//</span>
            </h2>
          </div>
          <Link href="/browse" class="reveal reveal-delay-2 inline-flex items-center gap-2 text-sm text-primary hover:text-primary/80 transition-colors group">
            عرض الكل
            <ArrowLeft class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
          </Link>
        </div>

        <div class="flex flex-col gap-3 items-center">
          <div class="grid grid-cols-3 gap-3 max-w-[75%]">
            <div
              v-for="(product, i) in firstRow"
              :key="product.id"
              class="reveal tilt-card group relative glass border border-primary/5 rounded-lg overflow-hidden transition-all duration-300 hover:border-primary/20"
              :class="`reveal-delay-${i}`"
            >
              <div class="aspect-[4/2] overflow-hidden relative">
                <img
                  :src="product.image || '/placeholder-product.png'"
                  :alt="product.name"
                  class="w-full h-full object-cover transition-all duration-700 group-hover:scale-105"
                  loading="lazy"
                  @error="($event.target as HTMLImageElement).src = '/placeholder-product.png'"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-background via-background/20 to-transparent" />
                <div v-if="product.badge" class="absolute top-1.5 right-1.5">
                  <span class="px-1.5 py-0.5 text-[8px] font-mono font-semibold uppercase tracking-wider bg-primary/15 text-primary border border-primary/20 rounded backdrop-blur-sm">
                    {{ product.badge }}
                  </span>
                </div>
                <div class="absolute top-1.5 left-1.5 w-2.5 h-2.5 border-t border-l border-primary/20 group-hover:border-primary/50 transition-colors" />
                <div class="absolute bottom-1.5 right-1.5 w-2.5 h-2.5 border-b border-r border-primary/20 group-hover:border-primary/50 transition-colors" />
              </div>
              <div class="p-2">
                <div class="flex items-center gap-1 mb-1">
                  <span class="text-[9px] font-mono text-muted-foreground uppercase tracking-wider">{{ product.category?.name || 'عام' }}</span>
                  <span class="w-0.5 h-0.5 rounded-full bg-muted-foreground/30" />
                  <span class="text-[9px] font-mono text-success uppercase tracking-wider flex items-center gap-0.5">
                    <span class="w-0.5 h-0.5 rounded-full bg-success animate-pulse" />
                    متوفر
                  </span>
                </div>
                <h3 class="text-xs font-semibold text-white group-hover:text-primary transition-colors mb-1 leading-tight">
                  {{ product.name }}
                </h3>
                <div class="flex items-center justify-between">
                  <span class="font-mono text-sm font-bold text-primary">${{ fmtPrice(product.price) }}</span>
                  <Link
                    :href="`/products/${product.id}`"
                    class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-medium bg-primary/10 text-primary border border-primary/20 rounded-md hover:bg-primary/20 hover:border-primary/40 transition-all"
                  >
                    <ShoppingCart class="w-2.5 h-2.5" />
                    اشترِ
                  </Link>
                </div>
              </div>
            </div>
          </div>
          <div v-if="secondRow.length" class="grid grid-cols-2 gap-3 max-w-[50%]">
            <div
              v-for="(product, i) in secondRow"
              :key="product.id"
              class="reveal tilt-card group relative glass border border-primary/5 rounded-lg overflow-hidden transition-all duration-300 hover:border-primary/20"
            >
              <div class="aspect-[4/2] overflow-hidden relative">
                <img
                  :src="product.image || '/placeholder-product.png'"
                  :alt="product.name"
                  class="w-full h-full object-cover transition-all duration-700 group-hover:scale-105"
                  loading="lazy"
                  @error="($event.target as HTMLImageElement).src = '/placeholder-product.png'"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-background via-background/20 to-transparent" />
                <div v-if="product.badge" class="absolute top-1.5 right-1.5">
                  <span class="px-1.5 py-0.5 text-[8px] font-mono font-semibold uppercase tracking-wider bg-primary/15 text-primary border border-primary/20 rounded backdrop-blur-sm">
                    {{ product.badge }}
                  </span>
                </div>
                <div class="absolute top-1.5 left-1.5 w-2.5 h-2.5 border-t border-l border-primary/20 group-hover:border-primary/50 transition-colors" />
                <div class="absolute bottom-1.5 right-1.5 w-2.5 h-2.5 border-b border-r border-primary/20 group-hover:border-primary/50 transition-colors" />
              </div>
              <div class="p-2">
                <div class="flex items-center gap-1 mb-1">
                  <span class="text-[9px] font-mono text-muted-foreground uppercase tracking-wider">{{ product.category?.name || 'عام' }}</span>
                  <span class="w-0.5 h-0.5 rounded-full bg-muted-foreground/30" />
                  <span class="text-[9px] font-mono text-success uppercase tracking-wider flex items-center gap-0.5">
                    <span class="w-0.5 h-0.5 rounded-full bg-success animate-pulse" />
                    متوفر
                  </span>
                </div>
                <h3 class="text-xs font-semibold text-white group-hover:text-primary transition-colors mb-1 leading-tight">
                  {{ product.name }}
                </h3>
                <div class="flex items-center justify-between">
                  <span class="font-mono text-sm font-bold text-primary">${{ fmtPrice(product.price) }}</span>
                  <Link
                    :href="`/products/${product.id}`"
                    class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-medium bg-primary/10 text-primary border border-primary/20 rounded-md hover:bg-primary/20 hover:border-primary/40 transition-all"
                  >
                    <ShoppingCart class="w-2.5 h-2.5" />
                    اشترِ
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ CATEGORIES ═══ -->
    <section id="categories" class="py-24 md:py-32 border-t border-primary/5">
      <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
          <span class="section-label reveal">تصفح</span>
          <h2 class="text-3xl md:text-4xl font-bold tracking-tight mt-3 reveal reveal-delay-1" style="font-family: 'Cairo', 'Orbitron', sans-serif;">
            تسوق حسب الفئة <span class="text-gradient">//</span>
          </h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <div
            v-for="(cat, i) in categoriesList"
            :key="cat.name"
            class="reveal group relative glass border border-primary/5 rounded-xl p-6 text-center cursor-pointer transition-all duration-300 hover:border-primary/20 card-hover"
            :class="`reveal-delay-${i}`"
          >
            <div class="absolute inset-0 rounded-xl bg-gradient-to-br opacity-0 group-hover:opacity-100 transition-opacity duration-500" :class="cat.color" />
            <div class="relative z-10">
              <div class="w-14 h-14 mx-auto mb-4 rounded-lg border border-primary/10 bg-primary/5 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <component :is="cat.icon" class="w-6 h-6 text-primary" />
              </div>
              <h3 class="font-semibold text-white mb-1">{{ cat.name }}</h3>
              <span class="text-xs font-mono text-muted-foreground">{{ cat.count }} منتج</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ HOW IT WORKS ═══ -->
    <section id="how" class="py-24 md:py-32 border-t border-primary/5">
      <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
          <span class="section-label reveal">الخطوات</span>
          <h2 class="text-3xl md:text-4xl font-bold tracking-tight mt-3 reveal reveal-delay-1" style="font-family: 'Cairo', 'Orbitron', sans-serif;">
            كيف يعمل <span class="text-gradient">//</span>
          </h2>
        </div>

        <div class="grid md:grid-cols-4 gap-6 relative">
          <!-- Connector Line (Desktop) -->
          <div class="hidden md:block absolute top-12 right-[12.5%] left-[12.5%] h-px bg-gradient-to-r from-transparent via-primary/20 to-transparent" />

          <div
            v-for="(step, i) in steps"
            :key="step.num"
            class="reveal relative"
            :class="`reveal-delay-${i}`"
          >
            <div class="glass border rounded-xl p-6 text-center transition-all duration-300 hover:border-primary/30" :class="step.border">
              <div class="w-12 h-12 mx-auto mb-4 rounded-lg flex items-center justify-center" :class="step.bg">
                <component :is="step.icon" class="w-5 h-5" :class="step.color" />
              </div>
              <div class="font-mono text-[10px] tracking-wider mb-2" :class="step.color">خطوة {{ step.num }}</div>
              <h3 class="font-semibold text-white mb-2">{{ step.title }}</h3>
              <p class="text-xs text-muted-foreground leading-relaxed">{{ step.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══ TRUST / CTA ═══ -->
    <section class="py-24 md:py-32 border-t border-primary/5 relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-b from-primary/5 to-transparent pointer-events-none" />
      <div class="max-w-4xl mx-auto px-6 text-center relative z-10">
        <span class="section-label reveal">جاهز؟</span>
        <h2 class="text-3xl md:text-5xl font-bold tracking-tight mt-6 leading-tight reveal reveal-delay-1" style="font-family: 'Cairo', 'Orbitron', sans-serif;">
          اشحن رصيدك.<br><span class="text-gradient">وانطلق في اللعب الآن.</span>
        </h2>
        <p class="mt-6 text-muted-foreground max-w-lg mx-auto leading-relaxed reveal reveal-delay-2">
          انضم إلى آلاف اللاعبين الذين يثقون بـ CoreS للتوصيل الفوري.
          لا انتظار. لا تعقيد. فقط لعب.
        </p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 reveal reveal-delay-3">
          <Link href="/register" class="magnetic inline-flex items-center gap-2 px-8 py-4 bg-primary text-primary-foreground font-semibold rounded-md hover:bg-primary/90 transition-all glow-primary">
            <Zap class="w-4 h-4" />
            إنشاء حساب مجاني
          </Link>
          <Link href="/deposit" class="magnetic inline-flex items-center gap-2 px-8 py-4 border border-primary/30 text-primary font-medium rounded-md hover:bg-primary/5 hover:border-primary/50 transition-all">
            <CreditCard class="w-4 h-4" />
            إيداع رصيد
          </Link>
        </div>
      </div>
    </section>

    <!-- ═══ FOOTER ═══ -->
    <footer class="border-t border-primary/10 py-12">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div>
            <div class="flex items-center gap-2.5">
              <div class="w-7 h-7 rounded-md flex items-center justify-center border border-primary/30 bg-primary/10">
                <Cpu class="w-3.5 h-3.5 text-primary" />
              </div>
              <span class="font-bold tracking-wider text-white" style="font-family: 'Orbitron', 'Cairo', sans-serif;">CoreS</span>
            </div>
            <p class="text-xs text-muted-foreground mt-2 max-w-sm leading-relaxed">
              سوق البطاقات الرقمية المتميز. توصيل فوري للاعبين، من لاعبين.
            </p>
          </div>
          <div class="flex items-center gap-6">
            <a href="#" class="text-muted-foreground hover:text-primary transition-colors" aria-label="Discord">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.248.195.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.419 0 1.334-.956 2.419-2.157 2.419zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.419 0 1.334-.946 2.419-2.157 2.419z"/></svg>
            </a>
            <a href="#" class="text-muted-foreground hover:text-primary transition-colors" aria-label="Twitter">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a href="#" class="text-muted-foreground hover:text-primary transition-colors" aria-label="Telegram">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 8.021-3.48 3.157-1.365 3.835-1.587 4.27-1.587z"/></svg>
            </a>
          </div>
        </div>
        <div class="mt-8 pt-6 border-t border-primary/5 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p class="text-[11px] text-muted-foreground">&copy; 2026 CoreS. جميع الحقوق محفوظة.</p>
          <p class="text-[11px] text-muted-foreground font-mono">مُبنى للاعبين. يعمل بالنيون.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* ── Reveal Animations ── */
.reveal {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}
.reveal.revealed {
  opacity: 1;
  transform: translateY(0);
}
.reveal-delay-1 { transition-delay: 0.1s; }
.reveal-delay-2 { transition-delay: 0.2s; }
.reveal-delay-3 { transition-delay: 0.3s; }
.reveal-delay-4 { transition-delay: 0.4s; }

/* ── Section Label ── */
.section-label {
  font-family: var(--font-mono), 'JetBrains Mono', monospace;
  font-size: 11px;
  font-weight: 500;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: hsl(var(--primary));
  opacity: 0.7;
}

/* ── Cursor Blink ── */
.cursor-blink::after {
  content: '█';
  animation: blink 1s step-end infinite;
  color: hsl(var(--primary));
  font-size: 10px;
  margin-right: 2px;
}
@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}

/* ── Magnetic buttons need will-change for performance ── */
.magnetic {
  will-change: transform;
  transition: transform 0.25s cubic-bezier(0.23, 1, 0.32, 1);
}

/* ── Tilt cards need preserve-3d ── */
.tilt-card {
  transform-style: preserve-3d;
  will-change: transform, box-shadow;
  transition: transform 0.15s ease-out, box-shadow 0.3s ease;
}

/* ── Arabic display font override ── */
.font-display {
  font-family: 'Cairo', 'Orbitron', 'Inter', sans-serif;
}
</style>