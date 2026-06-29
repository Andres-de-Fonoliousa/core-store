import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useStorage } from '@vueuse/core';
import { i18n, setLocale as setI18nLocale } from '@/i18n';

export const useUiStore = defineStore('ui', () => {
  const sidebarCollapsed = useStorage('sidebar-collapsed', false);
  const isLoading = ref(false);
  const pageTitle = ref('');
  const locale = useStorage<'en' | 'ar'>('app-locale', 'ar');

  const isRtl = computed(() => locale.value === 'ar');

  function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
  }

  function setLoading(value: boolean) {
    isLoading.value = value;
  }

  function setPageTitle(title: string) {
    pageTitle.value = title;
  }

  function setLocale(newLocale: 'en' | 'ar') {
    locale.value = newLocale;
    setI18nLocale(newLocale);
  }

  return {
    sidebarCollapsed,
    isLoading,
    pageTitle,
    locale,
    isRtl,
    toggleSidebar,
    setLoading,
    setPageTitle,
    setLocale,
  };
});
