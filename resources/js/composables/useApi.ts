import axios, { AxiosError, AxiosInstance } from 'axios';
import { router } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/authStore';

const api: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

api.interceptors.request.use((config) => {
  const authStore = useAuthStore();

  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`;
  }

  if (config.data instanceof FormData) {
    delete config.headers['Content-Type'];
  }

  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    if (error.response?.status === 401 && !window.location.pathname.startsWith('/login')) {
      const authStore = useAuthStore();
      authStore.logout();
      router.visit('/login');
    }

    return Promise.reject(error);
  },
);

export function useApi() {
  return api;
}
