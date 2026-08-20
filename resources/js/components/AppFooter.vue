<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Cpu, Compass, Home, ShieldCheck, LifeBuoy, FileText, Shield, CircleDollarSign } from 'lucide-vue-next';

const shopColumns = [
  { label: 'nav.home', href: '/', icon: Home },
  { label: 'nav.catalog', href: '/browse', icon: Compass },
  { label: 'nav.deposit', href: '/deposit', icon: CircleDollarSign },
];

const infoColumns = [
  { label: 'footer.about', href: '/about', icon: ShieldCheck },
  { label: 'footer.terms', href: '/terms', icon: FileText },
  { label: 'footer.privacy', href: '/privacy', icon: Shield },
  { label: 'footer.support', href: '/support', icon: LifeBuoy },
];
</script>

<template>
  <footer class="relative z-10 border-t border-primary/10 bg-background/40 backdrop-blur-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
        <!-- Brand -->
        <div class="col-span-2 md:col-span-2">
          <Link href="/" class="inline-flex items-center gap-2.5 group">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center border border-primary/30 bg-primary/10 glow-primary">
              <Cpu class="w-4 h-4 text-primary" />
            </div>
            <span class="font-display text-lg font-bold tracking-wider text-white group-hover:text-primary transition-colors">
              {{ $t('app.name') }}
            </span>
          </Link>
          <p class="mt-3 text-sm text-muted-foreground max-w-sm leading-relaxed">
            {{ $t('app.footerTagline') }}
          </p>
        </div>

        <!-- Shop -->
        <div>
          <h4 class="text-xs font-mono text-primary tracking-[0.2em] uppercase mb-3">{{ $t('nav.catalog') }}</h4>
          <div class="flex flex-col gap-2">
            <Link v-for="l in shopColumns" :key="l.href" :href="l.href" class="flex items-center gap-2 text-sm text-muted-foreground hover:text-primary transition-colors">
              <component :is="l.icon" class="w-3.5 h-3.5 opacity-70" />
              {{ $t(l.label) }}
            </Link>
          </div>
        </div>

        <!-- Info -->
        <div>
          <h4 class="text-xs font-mono text-primary tracking-[0.2em] uppercase mb-3">{{ $t('footer.info') }}</h4>
          <div class="flex flex-col gap-2">
            <Link v-for="l in infoColumns" :key="l.href" :href="l.href" class="flex items-center gap-2 text-sm text-muted-foreground hover:text-primary transition-colors">
              <component :is="l.icon" class="w-3.5 h-3.5 opacity-70" />
              {{ l.label }}
            </Link>
          </div>
        </div>
      </div>

      <div class="mt-10 pt-6 border-t border-primary/5 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-xs text-muted-foreground/70">
          &copy; {{ new Date().getFullYear() }} {{ $t('app.name') }}. {{ $t('auth.split.copyright') }}
        </p>
        <div class="flex items-center gap-5 text-xs text-muted-foreground/70">
          <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-success animate-pulse" /> {{ $t('app.systemStatus') }}</span>
        </div>
      </div>
    </div>
  </footer>
</template>
