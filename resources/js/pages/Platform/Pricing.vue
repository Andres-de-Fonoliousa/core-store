<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Check } from 'lucide-vue-next'

const { t } = useI18n()

const plans = [
  { key: 'free', nameKey: 'platform.pricing.free', price: '$0', features: ['platform.pricing.freeProducts', 'platform.pricing.freeUsers', 'platform.pricing.freeProductsLimit', 'platform.pricing.freeSupport'] },
  { key: 'pro', nameKey: 'platform.pricing.pro', price: '$29', features: ['platform.pricing.proDomain', 'platform.pricing.proUsers', 'platform.pricing.proProducts', 'platform.pricing.proFulfillment', 'platform.pricing.proSupport', 'platform.pricing.proFee'] },
  { key: 'enterprise', nameKey: 'platform.pricing.enterprise', price: '$99', features: ['platform.pricing.enterpriseInherit', 'platform.pricing.enterpriseProducts', 'platform.pricing.enterpriseApi', 'platform.pricing.enterpriseSupport', 'platform.pricing.enterpriseFee'] },
]
</script>

<template>
  <Head :title="t('platform.pricing.title')" />
  <div class="min-h-screen flex items-center justify-center py-24">
    <div class="max-w-6xl mx-auto px-4 w-full">
      <div class="text-center mb-16">
        <h1 class="text-4xl font-bold">{{ t('platform.pricing.title') }}</h1>
        <p class="text-white/50 mt-3">{{ t('platform.pricing.subtitle') }}</p>
      </div>
      <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
        <div v-for="plan in plans" :key="plan.key" class="p-8 rounded-2xl border border-white/5 bg-white/[0.02] flex flex-col">
          <h3 class="text-lg font-semibold mb-2">{{ t(plan.nameKey) }}</h3>
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
          <Link href="/create-store" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold text-sm bg-primary text-primary-foreground hover:bg-primary/90 transition-all">
            {{ t('platform.pricing.getStarted') }}
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>
