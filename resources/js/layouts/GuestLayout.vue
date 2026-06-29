<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Cpu } from 'lucide-vue-next';
import { home } from '@/routes';
</script>

<template>
  <Head>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />
  </Head>

  <div class="min-h-screen bg-[#050507] text-white relative isolate flex flex-col items-center justify-center p-6 overflow-x-hidden font-sans">
    <!-- Ambient -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]" />
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-cyan-500/10 blur-[120px] rounded-full" />
    </div>

    <!-- Optional minimal logo -->
    <Link :href="home()" class="absolute top-8 left-1/2 -translate-x-1/2 flex items-center gap-3 group z-10">
      <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-cyan-500/10 border border-cyan-400/30">
        <Cpu class="w-4 h-4 text-cyan-400" />
      </div>
      <span class="font-bold tracking-wider text-white text-sm font-display">{{ $t('app.name') }}</span>
    </Link>

    <div class="relative z-10 w-full max-w-lg">
      <slot />
    </div>
  </div>
</template>