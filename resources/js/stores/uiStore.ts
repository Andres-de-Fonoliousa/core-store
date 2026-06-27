import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useStorage } from '@vueuse/core';

export const useUiStore = defineStore('ui', () => {
  const sidebarCollapsed = useStorage('sidebar-collapsed', false);
  const isLoading = ref(false);
  const pageTitle = ref('');

  function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
  }

  function setLoading(value: boolean) {
    isLoading.value = value;
  }

  function setPageTitle(title: string) {
    pageTitle.value = title;
  }

  return {
    sidebarCollapsed,
    isLoading,
    pageTitle,
    toggleSidebar,
    setLoading,
    setPageTitle,
  };
});
