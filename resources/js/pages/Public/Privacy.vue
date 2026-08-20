<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Lock, Shield, Eye, Database, Trash2, FileText } from 'lucide-vue-next';
import AppNavbar from '@/components/AppNavbar.vue';
import AppFooter from '@/components/AppFooter.vue';

const sections = [
  {
    title: '1. البيانات التي نجمعها',
    icon: Database,
    content: 'نجمع البيانات الضرورية فقط: اسمك، بريدك الإلكتروني، رصيد محفظتك، وسجل طلباتك. لا نجمع بيانات بطاقات ائتمان — جميع المدفوعات تتم عبر الإيداعات البنكية.',
  },
  {
    title: '2. كيف نستخدم بياناتك',
    icon: Eye,
    content: 'نستخدم بياناتك لتشغيل المنصة وتوصيل المنتجات ودعمك الفني. لا نبيع بياناتك لأي طرف ثالث. لا نستخدمها للإعلانات المستهدفة.',
  },
  {
    title: '3. أمان البيانات',
    icon: Lock,
    content: 'جميع البيانات مشفرة أثناء النقل (SSL/TLS). كلمات المرور مخزنة باستخدام خوارزميات التجزئة الآمنة. نجري مراجعات أمان دورية.',
  },
  {
    title: '4. ملفات تعريف الارتباط',
    icon: Shield,
    content: 'نستخدم ملفات تعريف الارتباط الضرورية فقط لتسجيل الدخول والحفاظ على الجلسة. لا نستخدم ملفات تعقب أو تحليلات خارجية.',
  },
  {
    title: '5. حقوقك',
    icon: FileText,
    content: 'للك الحق في: (1) الوصول إلى بياناتك، (2) تصحيح أي معلومات غير دقيقة، (3) طلب حذف حسابك وبياناتك. تواصل معنا لأي طلب من هذه الطلبات.',
  },
  {
    title: '6. الاحتفاظ بالبيانات',
    icon: Trash2,
    content: 'نحتفظ ببياناتك طالما كان حسابك نشطاً. بعد حذف الحساب، نحتفظ بالسجلات المالية لمدة 5 سنوات لأسباب قانونية، ثم نحذفها نهائياً.',
  },
];
</script>

<template>
  <Head title="سياسة الخصوصية">
    <meta name="description" content="سياسة الخصوصية — كيف نحمي بياناتك ونتعامل مع معلوماتك الشخصية في متجر المنتجات الرقمية." />
    <meta property="og:title" content="سياسة الخصوصية | المتجر" />
    <meta property="og:description" content="سياسة الخصوصية — كيف نحمي بياناتك ونتعامل مع معلوماتك الشخصية." />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="ar_SY" />
    <meta name="twitter:card" content="summary" />
  </Head>

  <div class="min-h-screen bg-[#050507] text-white relative overflow-x-hidden font-sans">
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]" />
    </div>

    <div class="relative z-10">
      <!-- Navbar -->
      <AppNavbar />

      <!-- Hero -->
      <section class="relative w-full pt-32 pb-12 px-4 sm:px-6 lg:px-10 xl:px-16">
        <div class="max-w-3xl mx-auto">
          <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full border border-cyan-400/20 bg-cyan-500/5 mb-6">
            <Lock class="w-4 h-4 text-cyan-400" />
            <span class="text-xs font-mono text-cyan-400 tracking-wider uppercase">الخصوصية</span>
          </div>
          <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight leading-[1.1] mb-4 font-display">
            سياسة الخصوصية
          </h1>
          <p class="text-lg text-white/50 leading-relaxed">
            نحترم خصوصيتك. هذه السياسة توضح كيف نتعامل مع بياناتك.
          </p>
        </div>
      </section>

      <!-- Content -->
      <section class="w-full py-12 px-4 sm:px-6 lg:px-10 xl:px-16">
        <div class="max-w-3xl mx-auto space-y-6">
          <div
            v-for="(section, i) in sections"
            :key="i"
            class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl hover:border-white/10 transition-colors"
          >
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-lg bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center flex-shrink-0">
                <component :is="section.icon" class="w-4 h-4 text-cyan-400" />
              </div>
              <h2 class="text-lg font-bold text-white">{{ section.title }}</h2>
            </div>
            <p class="text-sm text-white/50 leading-relaxed pr-13">{{ section.content }}</p>
          </div>

          <!-- Contact -->
          <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 text-center">
            <h3 class="text-sm font-semibold text-white mb-2">أسئلة حول الخصوصية؟</h3>
            <p class="text-sm text-white/50 mb-4">تواصل معنا عبر البريد الإلكتروني</p>
            <a href="mailto:astroid198@gmail.com" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold bg-cyan-400/10 text-cyan-400 border border-cyan-400/20 rounded-xl hover:bg-cyan-400/20 transition-all">
              astroid198@gmail.com
            </a>
          </div>
        </div>
      </section>

      <!-- Footer -->
      <AppFooter />
    </div>
  </div>
</template>