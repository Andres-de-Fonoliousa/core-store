import { ref } from 'vue';
import { useTimeoutFn } from '@vueuse/core';

export function useClipboard() {
  const copied = ref(false);

  async function copy(text: string) {
    try {
      await navigator.clipboard.writeText(text);
      copied.value = true;

      useTimeoutFn(() => {
        copied.value = false;
      }, 2000);

      return true;
    } catch (error) {
      console.error('Clipboard copy failed', error);
      return false;
    }
  }

  return {
    copied,
    copy,
  };
}
