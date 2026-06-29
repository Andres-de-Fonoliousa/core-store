<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
  Cpu, ChevronDown, MessageCircle, Mail, Clock,
  HelpCircle, CreditCard, Package, Zap, Shield,
} from 'lucide-vue-next';

const faqs = ref([
  {
    q: 'كيف يعمل التوصيل الفوري؟',
    a: 'بمجرد تأكيد الدفع، يتم إرسال طلبك تلقائياً إلى مزود الخدمة. في معظم الحالات، يصلك المنتج خلال 30 ثانية.',
    icon: Zap,
    open: false,
  },
  {
    q: 'ما طرق الدفع المتاحة؟',
    a: 'يمكنك إيداع رصيد في محفظتك عبر التحويل البنكي أو المحافظ الإلكترونية. بعد الموافقة على الإيداع، يصبح الرصيد متاحاً للشراء فوراً.',
    icon: CreditCard,
    open: false,
  },
  {
    q: 'ماذا لو تأخر توصيل طلبي؟',
    a: 'إذا تجاوز التوصيل 5 دقائق، يرجى التواصل مع الدعم الفني. نتابع جميع الطلبات يدوياً لضمان وصولها.',
    icon: Clock,
    open: false,
  },
  {
    q: 'هل يمكنني استرجاع مشترياتي؟',
    a: 'الاسترجاع ممكن فقط إذا لم يتم توصيل المنتج أو كان به عطل تقني. المنتجات المُسلمة رقمياً غير قابلة للاسترجاع بمجرد الاستخدام.',
    icon: Package,
    open: false,
  },
  {
    q: 'كيف أضمن أمان معاملاتي؟',
    a: 'جميع المعاملات مشفرة بـ SSL. رصيدك محفوظ في محفظة داخلية ولا يمكن لأي طرف ثالث الوصول إليه.',
    icon: Shield,
    open: false,
  },
  {
    q: 'كيف أتواصل مع الدعم؟',
    a: 'يمكنك التواصل معنا عبر Telegram على مدار الساعة.',
    icon: MessageCircle,
    open: false,
  },
]);

const contactMethods = [
  { icon: Mail, label: 'البريد الإلكتروني', value: 'astroid198@gmail.com', href: 'mailto:astroid198@gmail.com' },
  { icon: MessageCircle, label: 'Telegram', value: '@Zenin0o', href: 'https://t.me/Zenin0o' },
];
</script>

<template>
  <Head title="الدعم والمساعدة">
    <meta name="description" content="الدعم الفني — إجابات للأسئلة الشائعة وطرق التواصل. نواصل معنا عبر Telegram أو البريد الإلكتروني." />
    <meta property="og:title" content="الدعم والمساعدة" />
    <meta property="og:description" content="الدعم الفني — إجابات للأسئلة الشائعة وطرق التواصل." />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="ar_SY" />
    <meta name="twitter:card" content="summary" />
  </Head>

  <div class="min-h-screen bg-[#050507] text-white relative overflow-x-hidden font-sans">
    <!-- Ambient -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]" />
      <div class="absolute top-[-10%] left-1/2 -translate-x-1/2 w-[80%] h-[40%] bg-cyan-500/10 blur-[120px] rounded-full" />
      <div class="absolute bottom-[10%] left-[-5%] w-[40%] h-[40%] bg-purple-600/10 blur-[120px] rounded-full" />
    </div>

    <div class="relative z-10">
      <!-- Navbar -->
      <nav class="fixed top-0 left-0 right-0 isolate z-40 bg-[#070709]/80 backdrop-blur-xl border-b border-white/5">
        <div class="w-full px-4 sm:px-6 lg:px-10 xl:px-16 h-20 flex items-center justify-between">
          <Link href="/" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-cyan-500/10 border border-cyan-400/30">
              <Cpu class="w-5 h-5 text-cyan-400" />
            </div>
            <span class="text-xl font-bold tracking-wider text-white font-display">{{ $t('app.name') }}</span>
          </Link>
          <div class="flex items-center gap-3">
            <Link href="/login" class="text-sm text-white/60 hover:text-white transition-colors px-4 py-2">تسجيل الدخول</Link>
            <Link href="/register" class="px-5 py-2 text-sm font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 transition-all">ابدأ الآن</Link>
          </div>
        </div>
      </nav>

      <!-- Hero -->
      <section class="relative w-full pt-32 pb-16 px-4 sm:px-6 lg:px-10 xl:px-16">
        <div class="max-w-3xl mx-auto text-center">
          <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full border border-cyan-400/20 bg-cyan-500/5 mb-6">
            <HelpCircle class="w-4 h-4 text-cyan-400" />
            <span class="text-xs font-mono text-cyan-400 tracking-wider uppercase">المركز</span>
          </div>
          <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight leading-[1.1] mb-4 font-display">
            كيف نساعدك؟
          </h1>
          <p class="text-lg text-white/50 max-w-xl mx-auto leading-relaxed">
            إجابات سريعة على الأسئلة الشائعة. إذا لم تجد ما تبحث عنه، فريق الدعم متاح على مدار الساعة.
          </p>
        </div>
      </section>

      <!-- FAQ -->
      <section class="w-full py-12 px-4 sm:px-6 lg:px-10 xl:px-16">
        <div class="max-w-3xl mx-auto">
          <div class="space-y-4">
            <div
              v-for="(faq, i) in faqs"
              :key="i"
              class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden transition-all duration-300"
              :class="faq.open ? 'border-cyan-400/20' : 'hover:border-white/10'"
            >
              <button
                @click="faq.open = !faq.open"
                class="w-full flex items-center gap-4 px-6 py-5 text-right transition-colors"
              >
                <div class="w-10 h-10 rounded-lg bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center flex-shrink-0">
                  <component :is="faq.icon" class="w-4 h-4 text-cyan-400" />
                </div>
                <span class="flex-1 text-sm font-semibold text-white">{{ faq.q }}</span>
                <ChevronDown
                  class="w-4 h-4 text-white/40 transition-transform duration-300 flex-shrink-0"
                  :class="faq.open ? 'rotate-180' : ''"
                />
              </button>
              <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="max-h-0 opacity-0" enter-to-class="max-h-40 opacity-100" leave-active-class="transition-all duration-200 ease-in" leave-from-class="max-h-40 opacity-100" leave-to-class="max-h-0 opacity-0">
                <div v-show="faq.open" class="overflow-hidden">
                  <div class="px-6 pb-5 pr-[72px]">
                    <p class="text-sm text-white/50 leading-relaxed">{{ faq.a }}</p>
                  </div>
                </div>
              </Transition>
            </div>
          </div>
        </div>
      </section>

      <!-- Contact -->
      <section class="w-full py-16 px-4 sm:px-6 lg:px-10 xl:px-16 border-t border-white/5">
        <div class="max-w-3xl mx-auto">
          <div class="text-center mb-12">
            <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">تواصل</span>
            <h2 class="text-3xl font-bold tracking-tight mt-3 font-display">طرق التواصل</h2>
          </div>
          <div class="grid sm:grid-cols-3 gap-5">
            <a
              v-for="method in contactMethods"
              :key="method.label"
              :href="method.href"
              class="group bg-white/[0.02] border border-white/5 rounded-2xl p-6 text-center hover:border-cyan-400/20 transition-all"
            >
              <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                <component :is="method.icon" class="w-5 h-5 text-cyan-400" />
              </div>
              <h3 class="text-sm font-semibold text-white mb-1">{{ method.label }}</h3>
              <p class="text-xs text-cyan-400 font-mono">{{ method.value }}</p>
            </a>
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
            <p class="text-[10px] text-white/30 font-mono">&copy; {{ new Date().getFullYear() }} {{ $t('app.name') }}. {{ $t('auth.split.copyright') }}</p>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>