<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { Cpu, ArrowLeft, Store, Mail, Lock, Check, Loader } from 'lucide-vue-next'
import { useToast } from '@/composables/useToast'

const { t } = useI18n()
const toast = useToast()
const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

function submit() {
  form.post('/platform/register', {
    onSuccess: () => {
      toast.success(t('platform.register.success'))
    },
    onError: (errors) => {
      toast.error(Object.values(errors).join(', '))
    },
  })
}
</script>

<template>
  <Head :title="t('platform.register.title')" />

  <div class="min-h-screen bg-background text-foreground flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-grid pointer-events-none z-0" />
    <div class="fixed inset-0 pointer-events-none z-0" style="background: radial-gradient(ellipse at 50% 0%, hsl(186 100% 55% / 0.04) 0%, transparent 60%)" />

    <div class="relative z-10 w-full max-w-md">
      <div class="text-center mb-8">
        <Link href="/" class="inline-flex items-center gap-2.5 group mb-3">
          <div class="w-10 h-10 rounded-lg flex items-center justify-center border border-primary/30 bg-primary/10">
            <Cpu class="w-5 h-5 text-primary" />
          </div>
          <span class="text-2xl font-bold tracking-wider text-white group-hover:text-primary transition-all font-orbitron">{{ t('app.name') }}</span>
        </Link>
        <h1 class="text-xl font-bold mt-4">{{ t('platform.register.title') }}</h1>
        <p class="text-sm text-white/50 mt-1">{{ t('platform.register.subtitle') }}</p>
      </div>

      <div class="glass border border-white/10 rounded-xl p-6 md:p-8 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-primary/30 to-transparent" />

        <form @submit.prevent="submit" class="flex flex-col gap-5">
          <div class="grid gap-2">
            <label class="text-sm font-medium text-white/80">{{ t('platform.register.storeName') }}</label>
            <div class="relative">
              <input
                v-model="form.name"
                type="text"
                :placeholder="t('platform.register.storeNamePlaceholder')"
                class="w-full h-11 pl-11 pr-4 bg-white/[0.02] border border-white/10 rounded-xl text-sm text-white placeholder:text-white/25 focus:outline-none focus:border-primary/50 transition-all"
              />
              <Store class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
            </div>
            <p v-if="form.errors.name" class="text-xs text-red-400">{{ form.errors.name }}</p>
          </div>

          <div class="grid gap-2">
            <label class="text-sm font-medium text-white/80">{{ t('platform.register.email') }}</label>
            <div class="relative">
              <input
                v-model="form.email"
                type="email"
                :placeholder="t('platform.register.emailPlaceholder')"
                class="w-full h-11 pl-11 pr-4 bg-white/[0.02] border border-white/10 rounded-xl text-sm text-white placeholder:text-white/25 focus:outline-none focus:border-primary/50 transition-all"
              />
              <Mail class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
            </div>
            <p v-if="form.errors.email" class="text-xs text-red-400">{{ form.errors.email }}</p>
          </div>

          <div class="grid gap-2">
            <label class="text-sm font-medium text-white/80">{{ t('platform.register.password') }}</label>
            <div class="relative">
              <input
                v-model="form.password"
                type="password"
                :placeholder="t('platform.register.passwordPlaceholder')"
                class="w-full h-11 pl-11 pr-4 bg-white/[0.02] border border-white/10 rounded-xl text-sm text-white placeholder:text-white/25 focus:outline-none focus:border-primary/50 transition-all"
              />
              <Lock class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
            </div>
            <p v-if="form.errors.password" class="text-xs text-red-400">{{ form.errors.password }}</p>
          </div>

          <div class="grid gap-2">
            <label class="text-sm font-medium text-white/80">{{ t('platform.register.confirmPassword') }}</label>
            <div class="relative">
              <input
                v-model="form.password_confirmation"
                type="password"
                :placeholder="t('platform.register.confirmPasswordPlaceholder')"
                class="w-full h-11 pl-11 pr-4 bg-white/[0.02] border border-white/10 rounded-xl text-sm text-white placeholder:text-white/25 focus:outline-none focus:border-primary/50 transition-all"
              />
              <Check class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
            </div>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="mt-2 w-full py-3 bg-primary text-primary-foreground font-semibold rounded-xl hover:bg-primary/90 transition-all glow-primary disabled:opacity-60 flex items-center justify-center gap-2"
          >
            <Loader v-if="form.processing" class="w-4 h-4 animate-spin" />
            {{ form.processing ? t('platform.register.submitting') : t('platform.register.submit') }}
          </button>
        </form>
      </div>

      <div class="text-center mt-6">
        <Link href="/" class="inline-flex items-center gap-2 text-xs text-white/40 hover:text-primary transition-colors">
          <ArrowLeft class="w-3.5 h-3.5" />
          {{ t('platform.register.backToHome') }}
        </Link>
      </div>
    </div>
  </div>
</template>
