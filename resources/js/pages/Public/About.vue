<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
  Cpu, Zap, Shield, Globe, Clock, Users, Gamepad2,
  ChevronLeft, ArrowLeft,
} from 'lucide-vue-next';

const values = [
  { icon: Zap, title: 'توصيل فوري', desc: 'شحن تلقائي في ثوانٍ. لا انتظار، لا تأخير.' },
  { icon: Shield, title: 'دفع آمن', desc: 'جميع المعاملات مشفرة. رصيدك محمي دائماً.' },
  { icon: Globe, title: 'تغطية عالمية', desc: 'منتجات رقمية من جميع أنحاء العالم بأسعار تنافسية.' },
  { icon: Clock, title: 'دعم 24/7', desc: 'فريق دعم متاح على مدار الساعة لمساعدتك.' },
];

const stats = [
  { value: '50K+', label: 'طلب منجز' },
  { value: '12K+', label: 'عميل سعيد' },
  { value: '99.9%', label: 'استقرار النظام' },
  { value: '30s', label: 'متوسط التوصيل' },
];
</script>

<template>
  <Head :title="'عن ' + $t('app.name')">
    <meta name="description" content="تعرف على المتجر — متجر رقمي رائد في حسابات الألعاب، اشتراكات المنصات، والشحن الرقمي. توصيل فوري، دعم 24/7، وأسعار تنافسية." />
    <meta property="og:title" content="عن المتجر | متجر رقمي للحسابات والاشتراكات" />
    <meta property="og:description" content="تعرف على المتجر — متجر رقمي رائد في حسابات الألعاب، اشتراكات المنصات، والشحن الرقمي. توصيل فوري، دعم 24/7." />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="ar_SY" />
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content="عن المتجر | متجر رقمي للحسابات والاشتراكات" />
  </Head>

  <div class="min-h-screen bg-[#050507] text-white relative overflow-x-hidden font-sans">
    <!-- Ambient -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]" />
      <div class="absolute top-[-10%] left-1/2 -translate-x-1/2 w-[80%] h-[40%] bg-cyan-500/10 blur-[120px] rounded-full" />
      <div class="absolute bottom-[10%] right-[-5%] w-[40%] h-[40%] bg-purple-600/10 blur-[120px] rounded-full" />
    </div>

    <div class="relative z-10">
      <!-- Navbar -->
      <nav class="fixed top-0 left-0 right-0 isolate z-40 transition-all duration-300 bg-[#070709]/80 backdrop-blur-xl border-b border-white/5">
        <div class="w-full px-4 sm:px-6 lg:px-10 xl:px-16 h-20 flex items-center justify-between">
          <Link href="/" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-cyan-500/10 border border-cyan-400/30">
              <Cpu class="w-5 h-5 text-cyan-400" />
            </div>
            <span class="text-xl font-bold tracking-wider text-white font-display">{{ $t('app.name') }}</span>
          </Link>
          <div class="flex items-center gap-3">
            <Link href="/login" class="text-sm text-white/60 hover:text-white transition-colors px-4 py-2">تسجيل الدخول</Link>
            <Link href="/register" class="px-5 py-2 text-sm font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 transition-all hover:shadow-[0_0_25px_-5px_rgba(34,211,238,0.5)]">ابدأ الآن</Link>
          </div>
        </div>
      </nav>

      <!-- Hero -->
      <section class="relative w-full pt-32 pb-20 px-4 sm:px-6 lg:px-10 xl:px-16">
        <div class="max-w-4xl mx-auto text-center">
          <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full border border-cyan-400/20 bg-cyan-500/5 mb-6">
            <Zap class="w-4 h-4 text-cyan-400" />
            <span class="text-xs font-mono text-cyan-400 tracking-wider uppercase">من نحن</span>
          </div>
          <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold tracking-tight leading-[1.1] mb-6 font-display">
            <span class="text-white">منصة</span>
            <span class="text-cyan-400"> {{ $t('app.name') }}</span>
          </h1>
          <p class="text-lg text-white/50 max-w-2xl mx-auto leading-relaxed">
            وجهتك الأولى للمنتجات الرقمية. نبني تجربة شراء سلسة للاعبين وعشاق التقنية —
            من بطاقات الألعاب إلى الاشتراكات الرقمية، التوصيل الفوري على مدار الساعة.
          </p>
        </div>
      </section>

      <!-- Stats -->
      <section class="w-full py-10 border-y border-white/5 bg-white/[0.02]">
        <div class="w-full px-4 sm:px-6 lg:px-10 xl:px-16">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
            <div v-for="stat in stats" :key="stat.label" class="text-center">
              <div class="text-3xl font-bold text-white font-mono tracking-tight mb-1">{{ stat.value }}</div>
              <span class="text-[11px] text-white/40 font-mono uppercase tracking-wider">{{ stat.label }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Values -->
      <section class="w-full py-24 px-4 sm:px-6 lg:px-10 xl:px-16">
        <div class="max-w-5xl mx-auto">
          <div class="text-center mb-16">
            <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">قيمنا</span>
            <h2 class="text-3xl md:text-4xl font-bold tracking-tight mt-3 font-display">لماذا {{ $t('app.name') }}؟</h2>
          </div>
          <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div v-for="v in values" :key="v.title" class="group relative bg-white/[0.02] border border-white/5 rounded-2xl p-6 hover:border-cyan-400/20 transition-all duration-300">
              <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-cyan-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
              <div class="relative">
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                  <component :is="v.icon" class="w-5 h-5 text-cyan-400" />
                </div>
                <h3 class="text-base font-bold text-white mb-2">{{ v.title }}</h3>
                <p class="text-sm text-white/40 leading-relaxed">{{ v.desc }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Terminal Aesthetic Section -->
      <section class="w-full py-24 px-4 sm:px-6 lg:px-10 xl:px-16 border-t border-white/5">
        <div class="max-w-4xl mx-auto">
          <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden backdrop-blur-xl">
            <div class="flex items-center gap-2 px-5 py-3 border-b border-white/5 bg-white/[0.02]">
              <div class="w-3 h-3 rounded-full bg-red-500/80" />
              <div class="w-3 h-3 rounded-full bg-amber-500/80" />
              <div class="w-3 h-3 rounded-full bg-emerald-500/80" />
              <span class="mr-auto text-[10px] font-mono text-white/30">cores://about</span>
            </div>
            <div class="p-6 font-mono text-sm space-y-2">
              <div class="flex items-center gap-2">
                <span class="text-cyan-400">&gt;</span>
                <span class="text-white/60">cat mission.txt</span>
              </div>
              <div class="pr-4 text-white/70 leading-relaxed">
                نؤمن بأن شراء المنتجات الرقمية يجب أن يكون سريعاً وآمناً وشفافاً.<br>
                لا أوراق معقدة. لا انتظار طويل. فقط اختر، ادفع، العب.
              </div>
              <div class="flex items-center gap-2 mt-4">
                <span class="text-cyan-400">&gt;</span>
                <span class="text-white/60">cat team.txt</span>
              </div>
              <div class="pr-4 text-white/70 leading-relaxed">
                فريق من المطورين واللاعبين. نبني ما نستخدمه أنفسنا.<br>
                مقرنا الرقمي — نخدم العالم.
              </div>
              <div class="mt-4">
                <span class="text-cyan-400">&gt;</span>
                <span class="cursor-blink" />
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section class="w-full py-24 px-4 sm:px-6 lg:px-10 xl:px-16 border-t border-white/5 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-cyan-500/5 to-transparent pointer-events-none" />
        <div class="max-w-2xl mx-auto text-center relative z-10">
          <h2 class="text-3xl md:text-4xl font-bold tracking-tight mb-4 font-display">
            جاهز للانضمام؟
          </h2>
          <p class="text-white/50 mb-8 leading-relaxed">
            آلاف اللاعبين يثقون بـ {{ $t('app.name') }} يومياً. انضم إليهم واستمتع بالتوصيل الفوري.
          </p>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link href="/register" class="px-8 py-3.5 bg-cyan-400 text-black rounded-xl font-semibold hover:bg-cyan-300 transition-all shadow-[0_0_30px_-5px_rgba(34,211,238,0.3)]">
              إنشاء حساب مجاني
            </Link>
            <Link href="/products" class="px-8 py-3.5 border border-white/10 text-white rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all">
              تصفح المنتجات
            </Link>
          </div>
        </div>
      </section>

      <!-- Footer -->
      <footer class="w-full py-10 border-t border-white/5">
        <div class="w-full px-4 sm:px-6 lg:px-10 xl:px-16">
          <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center border border-cyan-400/30 bg-cyan-500/10">
                <Cpu class="w-4 h-4 text-cyan-400" />
              </div>
              <div>
                <span class="font-bold tracking-wider text-white text-sm font-display">{{ $t('app.name') }}</span>
                <p class="text-[10px] text-white/40 font-mono">منصة المنتجات الرقمية</p>
              </div>
            </div>
            <div class="flex items-center gap-6 text-xs text-white/30 font-mono">
              <Link href="/about" class="hover:text-cyan-400 transition-colors">عنّا</Link>
              <Link href="/support" class="hover:text-cyan-400 transition-colors">الدعم</Link>
              <Link href="/terms" class="hover:text-cyan-400 transition-colors">الشروط</Link>
              <Link href="/privacy" class="hover:text-cyan-400 transition-colors">الخصوصية</Link>
            </div>
            <p class="text-[10px] text-white/30 font-mono">&copy; 2026 {{ $t('app.name') }}. جميع الحقوق محفوظة.</p>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>

<style scoped>
.cursor-blink::after {
  content: '█';
  animation: blink 1s step-end infinite;
  color: rgb(34, 211, 238);
  font-size: 10px;
  margin-right: 2px;
}
@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}
</style>