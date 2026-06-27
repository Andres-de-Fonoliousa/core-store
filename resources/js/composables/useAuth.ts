import { computed } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import { useApi } from './useApi';
import type { LoginRequest, RegisterRequest } from '@/types/api';

export function useAuth() {
  const store = useAuthStore();
  const api = useApi();

  const isAuthenticated = computed(() => !!store.user && !!store.token);
  const isAdmin = computed(() => store.user?.role === 'admin');
  const user = computed(() => store.user);

  async function login(credentials: LoginRequest) {
    const { data } = await api.post('/auth/login', credentials);
    store.setAuth(data.token, data.user);
    return data;
  }

  async function register(credentials: RegisterRequest) {
    const { data } = await api.post('/auth/register', credentials);
    store.setAuth(data.token, data.user);
    return data;
  }

  async function logout() {
    await api.post('/auth/logout');
    store.logout();
  }

  return {
    user,
    isAuthenticated,
    isAdmin,
    login,
    register,
    logout,
  };
}
