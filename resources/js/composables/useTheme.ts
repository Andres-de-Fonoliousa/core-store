import { ref, watch } from 'vue';
import { usePreferredDark, useStorage } from '@vueuse/core';

export type Theme = 'light' | 'dark' | 'system';

export function useTheme() {
  const theme = useStorage<Theme>('app-theme', 'system');
  const isDark = ref(false);
  const prefersDark = usePreferredDark();

  function applyTheme() {
    const shouldUseDark = theme.value === 'dark' || (theme.value === 'system' && prefersDark.value);
    isDark.value = shouldUseDark;

    document.documentElement.classList.toggle('dark', shouldUseDark);
  }

  watch([theme, prefersDark], applyTheme, { immediate: true });

  function setTheme(value: Theme) {
    theme.value = value;
  }

  return {
    theme,
    isDark,
    setTheme,
  };
}
