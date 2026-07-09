<script setup lang="ts">
import { onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useShopStore } from '@/stores/shopStore'

const { t } = useI18n()
const shopStore = useShopStore()
const page = usePage()

onMounted(() => {
  shopStore.hydrate(page.props)
})
</script>

<template>
  <div class="platform-site min-h-screen bg-background text-foreground">
    <!-- Nav -->
    <header class="fixed top-0 left-0 right-0 z-50 border-b border-white/5 bg-background/80 backdrop-blur-xl">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <Link href="/" class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center border border-primary/30 bg-primary/10">
              <span class="text-primary font-bold text-sm">C</span>
            </div>
            <span class="text-lg font-bold tracking-wider text-white">{{ t('app.name') }}</span>
          </Link>
          <nav class="flex items-center gap-6">
            <Link href="/pricing" class="text-sm text-white/60 hover:text-white transition-colors">{{ t('platform.nav.pricing') }}</Link>
            <Link href="/features" class="text-sm text-white/60 hover:text-white transition-colors">{{ t('platform.nav.features') }}</Link>
            <Link href="/login" class="text-sm text-white/60 hover:text-white transition-colors">{{ t('platform.nav.login') }}</Link>
            <Link
              href="/create-store"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-primary/10 text-primary border border-primary/20 rounded-lg hover:bg-primary/20 transition-all"
            >
              {{ t('platform.nav.getStarted') }}
            </Link>
          </nav>
        </div>
      </div>
    </header>

    <!-- Page content -->
    <main class="pt-16">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/5 py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
          <p class="text-sm text-white/40">&copy; {{ new Date().getFullYear() }} {{ t('app.name') }}. {{ t('auth.split.copyright') }}</p>
          <div class="flex items-center gap-6">
            <Link href="/terms" class="text-sm text-white/40 hover:text-white/60 transition-colors">{{ t('nav.home') }}</Link>
            <Link href="/privacy" class="text-sm text-white/40 hover:text-white/60 transition-colors">{{ t('nav.products') }}</Link>
            <Link href="/support" class="text-sm text-white/40 hover:text-white/60 transition-colors">{{ t('nav.orders') }}</Link>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>
