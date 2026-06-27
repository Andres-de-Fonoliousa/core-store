import { computed } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import { useApi } from './useApi';

export function useBalance() {
  const store = useAuthStore();
  const api = useApi();

  const balance = computed(() => store.user?.balance ?? 0);
  const formattedBalance = computed(() =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(balance.value),
  );

  async function refreshBalance() {
    try {
      const { data } = await api.get('/user');
      if (data && data.user) {
        store.setUser(data.user);
      }
    } catch (_error) {
      // ignore refresh errors silently
    }
  }

  return {
    balance,
    formattedBalance,
    refreshBalance,
  };
}
