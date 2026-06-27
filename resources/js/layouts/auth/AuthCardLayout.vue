<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Cpu, Zap } from 'lucide-vue-next';
import { home } from '@/routes';

defineProps<{ title?: string; description?: string }>();
</script>

<template>
  <Head>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet" />
  </Head>

  <div class="min-h-screen bg-[#050507] text-white relative flex items-center justify-center p-6 md:p-10 overflow-x-hidden" style="font-family: 'Space Grotesk', 'Cairo', sans-serif;">
    <!-- Ambient -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]" />
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-cyan-500/10 blur-[120px] rounded-full" />
      <div class="absolute bottom-0 right-0 w-[300px] h-[300px] bg-purple-600/10 blur-[120px] rounded-full" />
    </div>

    <div class="relative z-10 w-full max-w-md flex flex-col gap-8">
      <!-- Logo -->
      <Link :href="home()" class="flex items-center gap-3 self-center group">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-cyan-500/10 border border-cyan-400/30">
          <Cpu class="w-5 h-5 text-cyan-400" />
        </div>
        <span class="font-bold tracking-wider text-white text-xl" style="font-family: 'Space Grotesk', sans-serif;">CoreS</span>
      </Link>

      <!-- Card -->
      <div class="bg-white/[0.02] border border-white/5 rounded-2xl backdrop-blur-xl p-8 space-y-6 shadow-[0_0_60px_-15px_rgba(34,211,238,0.08)]">
        <div class="text-center space-y-2">
          <h1 class="text-xl font-semibold text-white">{{ title }}</h1>
          <p class="text-sm text-white/40">{{ description }}</p>
        </div>
        <slot />
      </div>

      <!-- Footer -->
      <p class="text-center text-[11px] text-white/30 font-mono flex items-center justify-center gap-1.5">
        <Zap class="w-3 h-3 text-cyan-400/50" /> مُبنى للاعبين. يعمل بالنيون.
      </p>
    </div>
  </div>
</template>