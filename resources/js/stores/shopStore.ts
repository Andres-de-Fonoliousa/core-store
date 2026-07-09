import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export interface Shop {
  id: string
  name: string
  logo: string | null
  favicon: string | null
  color: string
  colorDark: string
  locale: string
  currency: string
  plan: string
  status: string
}

export const useShopStore = defineStore('shop', () => {
  const shop = ref<Shop | null>(null)
  const isPlatform = ref(false)

  function hydrate(pageProps: any) {
    shop.value = pageProps?.shop ?? null
    isPlatform.value = pageProps?.platform ?? false
  }

  const name = computed(() => shop.value?.name ?? '')
  const logo = computed(() => shop.value?.logo ?? '')
  const favicon = computed(() => shop.value?.favicon ?? '')
  const brandColor = computed(() => shop.value?.color ?? '#22d3ee')
  const brandColorDark = computed(() => shop.value?.colorDark ?? '#06b6d4')
  const plan = computed(() => shop.value?.plan ?? 'free')
  const currency = computed(() => shop.value?.currency ?? 'USD')
  const locale = computed(() => shop.value?.locale ?? 'en')

  const isProOrHigher = computed(() => plan.value === 'pro' || plan.value === 'enterprise')
  const isActive = computed(() => shop.value?.status === 'active' || shop.value?.status === 'trial')

  return {
    shop, isPlatform, hydrate,
    name, logo, favicon, brandColor, brandColorDark,
    plan, currency, locale, isProOrHigher, isActive,
  }
})
