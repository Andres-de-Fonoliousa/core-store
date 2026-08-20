<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { FileText, AlertTriangle } from 'lucide-vue-next';
import AppNavbar from '@/components/AppNavbar.vue';
import AppFooter from '@/components/AppFooter.vue';

const sections = [
  {
    title: '1. المقدمة',
    content: 'بتصفحك أو استخدامك للمنصة، فإنك توافق على الالتزام بهذه الشروط والأحكام.',
  },
  {
    title: '2. المنتجات الرقمية',
    content: 'جميع المنتجات المعروضة على المنصة رقمية 100% ولا يوجد شحن فعلي. بمجرد توصيل المنتج وإرسال الكود أو الاشتراك، لا يمكن استرجاع المبلغ إلا في حالات تقنية محددة.',
  },
  {
    title: '3. عملية الشراء',
    content: 'يتم الشراء عبر رصيد محفظتك الداخلية. يجب إيداع رصيد أولاً وانتظار موافقة الإدارة. بمجرد تأكيد الإيداع، يصبح الرصيد متاحاً للشراء الفوري.',
  },
  {
    title: '4. التوصيل والاستلام',
    content: 'التوصيل تلقائي في معظم الحالات. إذا واجهت أي تأخير، يرجى التواصل مع الدعم خلال 24 ساعة. نحن غير مسؤولين عن تأخيرات خارجة عن إرادتنا من مزودي الخدمة.',
  },
  {
    title: '5. الاسترجاع والاستبدال',
    content: 'الاسترجاع ممكن فقط في الحالات التالية: (1) لم يتم توصيل المنتج نهائياً، (2) المنتج معطل تقنياً ولم يعمل منذ اللحظة الأولى. لا يمكن استرجاع المنتجات المُستخدمة.',
  },
  {
    title: '6. حساب المستخدم',
    content: 'أنت مسؤول عن الحفاظ على سرية بيانات حسابك. أي عملية شراء تتم من حسابك تعتبر مسؤوليتك. يجب إبلاغنا فوراً في حال الاشتباه باختراق الحساب.',
  },
  {
    title: '7. حدود المسؤولية',
    content: 'المنصة وسيط بينك وبين مزودي الخدمة. نبذل قصارى جهدنا لضمان جودة المنتجات، لكننا لا نتحمل مسؤولية أعطال خارجة عن سيطرتنا في أنظمة المزودين.',
  },
  {
    title: '8. التعديلات',
    content: 'نحتفظ بالحق في تعديل هذه الشروط في أي وقت. سيتم إشعار المستخدمين بالتغييرات الجوهرية. الاستمرار في استخدام المنصة بعد التعديل يعني القبول بها.',
  },
];
</script>

<template>
  <Head title="الشروط والأحكام">
    <meta name="description" content="الشروط والأحكام الخاصة باستخدام المنصة للمنتجات الرقمية. تصفح وشراء واسترجاع — تعرف على حقوقك والتزاماتك." />
    <meta property="og:title" content="الشروط والأحكام | المتجر" />
    <meta property="og:description" content="الشروط والأحكام الخاصة باستخدام المنصة للمنتجات الرقمية." />
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
            <FileText class="w-4 h-4 text-cyan-400" />
            <span class="text-xs font-mono text-cyan-400 tracking-wider uppercase">الشروط</span>
          </div>
          <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight leading-[1.1] mb-4 font-display">
            الشروط والأحكام
          </h1>
          <p class="text-lg text-white/50 leading-relaxed">
            باستخدام المنصة، فإنك توافق على هذه الشروط. يرجى قراءتها بعناية.
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
                <span class="text-sm font-bold text-cyan-400 font-mono">{{ i + 1 }}</span>
              </div>
              <h2 class="text-lg font-bold text-white">{{ section.title }}</h2>
            </div>
            <p class="text-sm text-white/50 leading-relaxed pr-13">{{ section.content }}</p>
          </div>

          <!-- Acceptance -->
          <div class="bg-amber-500/5 border border-amber-400/20 rounded-2xl p-6 flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 border border-amber-400/20 flex items-center justify-center flex-shrink-0">
              <AlertTriangle class="w-5 h-5 text-amber-400" />
            </div>
            <div>
              <h3 class="text-sm font-semibold text-white mb-1">ملاحظة هامة</h3>
              <p class="text-sm text-white/50 leading-relaxed">
                هذه الشروط قد يتم تحديثها من وقت لآخر. سيتم إشعار المستخدمين المسجلين بأي تغييرات جوهرية عبر البريد الإلكتروني.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Footer -->
      <AppFooter />
    </div>
  </div>
</template>
