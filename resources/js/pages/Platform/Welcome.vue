<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Cpu, Zap, Shield, Sparkles, TrendingUp, Users, Package, ChevronRight, Check } from 'lucide-vue-next'

const { t } = useI18n()

const plans = [
  { key: 'free', name: t('platform.pricing.free'), price: '$0', features: ['platform.pricing.freeProducts', 'platform.pricing.freeUsers', 'platform.pricing.freeProductsLimit', 'platform.pricing.freeSupport'] },
  { key: 'pro', name: t('platform.pricing.pro'), price: '$29', features: ['platform.pricing.proDomain', 'platform.pricing.proUsers', 'platform.pricing.proProducts', 'platform.pricing.proFulfillment', 'platform.pricing.proSupport', 'platform.pricing.proFee'] },
  { key: 'enterprise', name: t('platform.pricing.enterprise'), price: '$99', features: ['platform.pricing.enterpriseInherit', 'platform.pricing.enterpriseProducts', 'platform.pricing.enterpriseApi', 'platform.pricing.enterpriseSupport', 'platform.pricing.enterpriseFee'] },
]

const faqs = [
  { q: 'platform.faq.q1', a: 'platform.faq.a1' },
  { q: 'platform.faq.q2', a: 'platform.faq.a2' },
  { q: 'platform.faq.q3', a: 'platform.faq.a3' },
  { q: 'platform.faq.q4', a: 'platform.faq.a4' },
]

const activeFaq = ref<number | null>(null)
</script>

<template>
  <Head>
    <title>CoreS - {{ t('platform.hero.subtitle') }}</title>
  </Head>

  <!-- Hero -->
  <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <div class="fixed inset-0 bg-grid pointer-events-none" />
    <div class="fixed inset-0 pointer-events-none" style="background: radial-gradient(ellipse at 50% 0%, hsl(186 100% 55% / 0.06) 0%, transparent 60%)" />
    <div class="relative z-10 max-w-5xl mx-auto px-4 text-center py-32">
      <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-primary/20 bg-primary/5 text-primary text-xs font-mono mb-8">
        <Sparkles class="w-3.5 h-3.5" /> {{ t('platform.hero.badge') }}
      </div>
      <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold tracking-tight mb-6">
        {{ t('platform.hero.title', { highlight: t('platform.hero.highlight') }) }}
      </h1>
      <p class="text-lg md:text-xl text-white/50 max-w-2xl mx-auto mb-10">
        {{ t('platform.hero.subtitle') }}
      </p>
      <div class="flex items-center justify-center gap-4">
        <Link href="/create-store" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-primary-foreground font-semibold rounded-xl hover:bg-primary/90 transition-all glow-primary">
          {{ t('platform.hero.cta') }} <ChevronRight class="w-4 h-4" />
        </Link>
        <Link href="/pricing" class="inline-flex items-center gap-2 px-8 py-4 bg-white/[0.02] border border-white/10 text-white/80 font-semibold rounded-xl hover:border-primary/30 transition-all">
          {{ t('platform.hero.viewPricing') }}
        </Link>
      </div>
    </div>
  </section>

  <!-- Features Grid -->
  <section class="relative py-24 border-t border-white/5">
    <div class="max-w-6xl mx-auto px-4">
      <div class="text-center mb-16">
        <span class="text-xs font-mono text-primary tracking-[0.2em] uppercase">{{ t('platform.features.title') }}</span>
        <h2 class="text-3xl md:text-4xl font-bold mt-3">{{ t('platform.features.title') }}</h2>
        <p class="text-white/40 mt-3 max-w-xl mx-auto">{{ t('platform.features.subtitle') }}</p>
      </div>
      <div class="grid md:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl border border-white/5 bg-white/[0.02] hover:border-primary/20 transition-all">
          <div class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center mb-4">
            <Zap class="w-6 h-6 text-primary" />
          </div>
          <h3 class="text-lg font-semibold mb-2">{{ t('platform.features.autoFulfillment') }}</h3>
          <p class="text-sm text-white/50">{{ t('platform.features.autoFulfillmentDesc') }}</p>
        </div>
        <div class="p-6 rounded-2xl border border-white/5 bg-white/[0.02] hover:border-primary/20 transition-all">
          <div class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center mb-4">
            <Shield class="w-6 h-6 text-primary" />
          </div>
          <h3 class="text-lg font-semibold mb-2">{{ t('platform.features.securePayments') }}</h3>
          <p class="text-sm text-white/50">{{ t('platform.features.securePaymentsDesc') }}</p>
        </div>
        <div class="p-6 rounded-2xl border border-white/5 bg-white/[0.02] hover:border-primary/20 transition-all">
          <div class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center mb-4">
            <TrendingUp class="w-6 h-6 text-primary" />
          </div>
          <h3 class="text-lg font-semibold mb-2">{{ t('platform.features.analytics') }}</h3>
          <p class="text-sm text-white/50">{{ t('platform.features.analyticsDesc') }}</p>
        </div>
        <div class="p-6 rounded-2xl border border-white/5 bg-white/[0.02] hover:border-primary/20 transition-all">
          <div class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center mb-4">
            <Users class="w-6 h-6 text-primary" />
          </div>
          <h3 class="text-lg font-semibold mb-2">{{ t('platform.features.teamManagement') }}</h3>
          <p class="text-sm text-white/50">{{ t('platform.features.teamManagementDesc') }}</p>
        </div>
        <div class="p-6 rounded-2xl border border-white/5 bg-white/[0.02] hover:border-primary/20 transition-all">
          <div class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center mb-4">
            <Package class="w-6 h-6 text-primary" />
          </div>
          <h3 class="text-lg font-semibold mb-2">{{ t('platform.features.productManagement') }}</h3>
          <p class="text-sm text-white/50">{{ t('platform.features.productManagementDesc') }}</p>
        </div>
        <div class="p-6 rounded-2xl border border-white/5 bg-white/[0.02] hover:border-primary/20 transition-all">
          <div class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center mb-4">
            <Cpu class="w-6 h-6 text-primary" />
          </div>
          <h3 class="text-lg font-semibold mb-2">{{ t('platform.features.customDomain') }}</h3>
          <p class="text-sm text-white/50">{{ t('platform.features.customDomainDesc') }}</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Pricing -->
  <section class="relative py-24 border-t border-white/5" id="pricing">
    <div class="max-w-6xl mx-auto px-4">
      <div class="text-center mb-16">
        <span class="text-xs font-mono text-primary tracking-[0.2em] uppercase">{{ t('platform.pricing.title') }}</span>
        <h2 class="text-3xl md:text-4xl font-bold mt-3">{{ t('platform.pricing.title') }}</h2>
        <p class="text-white/40 mt-3">{{ t('platform.pricing.subtitle') }}</p>
      </div>
      <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
        <div v-for="plan in plans" :key="plan.key" class="p-8 rounded-2xl border border-white/5 bg-white/[0.02] hover:border-primary/20 transition-all flex flex-col">
          <h3 class="text-lg font-semibold mb-2">{{ plan.name }}</h3>
          <div class="mb-6">
            <span class="text-4xl font-bold">{{ plan.price }}</span>
            <span v-if="plan.key !== 'free'" class="text-white/40 text-sm">{{ t('platform.pricing.perMonth') }}</span>
          </div>
          <ul class="space-y-3 mb-8 flex-1">
            <li v-for="f in plan.features" :key="f" class="flex items-start gap-2 text-sm text-white/60">
              <Check class="w-4 h-4 text-primary mt-0.5 shrink-0" />
              {{ t(f) }}
            </li>
          </ul>
          <Link href="/create-store" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-sm transition-all" :class="plan.key === 'pro' ? 'bg-primary text-primary-foreground hover:bg-primary/90 glow-primary' : 'bg-white/5 text-white/80 border border-white/10 hover:border-primary/30'">
            {{ t('platform.pricing.getStarted') }}
          </Link>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="relative py-24 border-t border-white/5">
    <div class="max-w-3xl mx-auto px-4">
      <div class="text-center mb-12">
        <span class="text-xs font-mono text-primary tracking-[0.2em] uppercase">{{ t('platform.faq.title') }}</span>
        <h2 class="text-3xl md:text-4xl font-bold mt-3">{{ t('platform.faq.title') }}</h2>
      </div>
      <div class="space-y-4">
        <div v-for="(faq, i) in faqs" :key="i" class="border border-white/5 rounded-xl overflow-hidden">
          <button @click="activeFaq = activeFaq === i ? null : i" class="w-full flex items-center justify-between px-6 py-4 text-left text-sm font-medium hover:bg-white/[0.02] transition-all">
            {{ t(faq.q) }}
            <ChevronRight class="w-4 h-4 transition-transform" :class="activeFaq === i ? 'rotate-90' : ''" />
          </button>
          <div v-if="activeFaq === i" class="px-6 pb-4 text-sm text-white/50">
            {{ t(faq.a) }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
