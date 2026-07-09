<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Zap, Shield, TrendingUp, Users, Package, Cpu } from 'lucide-vue-next'

const { t } = useI18n()

const features = [
  { icon: Zap, titleKey: 'platform.features.autoFulfillment', descKey: 'platform.features.autoFulfillmentDesc' },
  { icon: Shield, titleKey: 'platform.features.securePayments', descKey: 'platform.features.securePaymentsDesc' },
  { icon: TrendingUp, titleKey: 'platform.features.analytics', descKey: 'platform.features.analyticsDesc' },
  { icon: Users, titleKey: 'platform.features.teamManagement', descKey: 'platform.features.teamManagementDesc' },
  { icon: Package, titleKey: 'platform.features.productManagement', descKey: 'platform.features.productManagementDesc' },
  { icon: Cpu, titleKey: 'platform.features.customDomain', descKey: 'platform.features.customDomainDesc' },
]
</script>

<template>
  <Head :title="t('platform.features.title')" />
  <div class="min-h-screen py-24">
    <div class="max-w-6xl mx-auto px-4">
      <div class="text-center mb-16">
        <h1 class="text-4xl font-bold">{{ t('platform.features.title') }}</h1>
        <p class="text-white/50 mt-3 max-w-xl mx-auto">{{ t('platform.features.subtitle') }}</p>
      </div>
      <div class="grid md:grid-cols-3 gap-6">
        <div v-for="f in features" :key="f.titleKey" class="p-6 rounded-2xl border border-white/5 bg-white/[0.02] hover:border-primary/20 transition-all">
          <div class="w-12 h-12 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center mb-4">
            <component :is="f.icon" class="w-6 h-6 text-primary" />
          </div>
          <h3 class="text-lg font-semibold mb-2">{{ t(f.titleKey) }}</h3>
          <p class="text-sm text-white/50">{{ t(f.descKey) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
