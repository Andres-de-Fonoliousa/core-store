<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Cpu, Zap, ShieldCheck, Clock, Globe } from 'lucide-vue-next';
import { home } from '@/routes';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';

const page = usePage();
const name = page.props.name;

defineProps<{ title?: string; description?: string }>();
</script>

<template>
  <Head>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />
  </Head>

  <div class="min-h-screen bg-[#050507] text-white relative grid lg:grid-cols-2 overflow-x-hidden font-sans">
    <!-- Language Switcher -->
    <div class="absolute top-4 left-4 z-20">
      <LanguageSwitcher />
    </div>
    <!-- Left Panel -->
    <div class="relative hidden lg:flex flex-col p-10 border-r border-white/5 overflow-hidden">
      <!-- Ambient -->
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] opacity-50" />
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[80%] h-[50%] bg-cyan-500/15 blur-[120px] rounded-full" />
        <div class="absolute bottom-0 left-0 w-[60%] h-[40%] bg-purple-600/10 blur-[100px] rounded-full" />
      </div>

      <div class="relative z-10 flex flex-col h-full">
        <Link :href="home()" class="flex items-center gap-3 group w-fit">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-cyan-500/10 border border-cyan-400/30">
            <Cpu class="w-5 h-5 text-cyan-400" />
          </div>
            <span class="font-bold tracking-wider text-white text-xl font-display">{{ $t('app.name') }}</span>
        </Link>

        <div class="flex-1 flex flex-col justify-center max-w-md gap-8">
          <h2 class="text-4xl font-bold leading-tight">{{ $t('auth.split.heading') }}</h2>
          <p class="text-white/50 leading-relaxed">{{ $t('auth.split.description') }}</p>
          <div class="flex items-center gap-6 text-sm text-white/40">
            <span class="flex items-center gap-2"><ShieldCheck class="w-4 h-4 text-emerald-400" /> {{ $t('auth.split.securePayment') }}</span>
            <span class="flex items-center gap-2"><Zap class="w-4 h-4 text-cyan-400" /> {{ $t('auth.split.instantDelivery') }}</span>
            <span class="flex items-center gap-2"><Globe class="w-4 h-4 text-purple-400" /> {{ $t('auth.split.countries') }}</span>
          </div>
        </div>

        <p class="text-[11px] text-white/30 font-mono">© {{ new Date().getFullYear() }} {{ $t('app.name') }}. {{ $t('auth.split.copyright') }}</p>
      </div>
    </div>

    <!-- Right Panel -->
    <div class="relative flex flex-col items-center justify-center p-6 md:p-10">
      <div class="absolute inset-0 pointer-events-none lg:hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] opacity-50" />
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[80%] h-[50%] bg-cyan-500/10 blur-[120px] rounded-full" />
      </div>

      <div class="relative z-10 w-full max-w-sm space-y-6">
        <div class="lg:hidden text-center space-y-2 mb-8">
          <div class="flex items-center justify-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-cyan-500/10 border border-cyan-400/30">
              <Cpu class="w-5 h-5 text-cyan-400" />
            </div>
          <span class="font-bold tracking-wider text-white text-xl font-display">{{ $t('app.name') }}</span>
          </div>
        </div>

        <div class="text-center space-y-2">
          <h1 class="text-xl font-semibold text-white" v-if="title">{{ title }}</h1>
          <p class="text-sm text-white/40" v-if="description">{{ description }}</p>
        </div>

        <slot />
      </div>
    </div>
  </div>
</template>