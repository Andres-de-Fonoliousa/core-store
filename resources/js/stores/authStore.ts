import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useStorage } from '@vueuse/core';
import type { User } from '@/types/models';

export const useAuthStore = defineStore('auth', () => {
  const token = useStorage<string | null>('auth-token', null);
  const user = ref<User | null>(null);

  const isAuthenticated = computed(() => !!token.value && !!user.value);
  const isAdmin = computed(() => user.value?.role === 'admin');

  function setAuth(newToken: string, newUser: User) {
    token.value = newToken;
    user.value = newUser;
  }

  function setUser(newUser: User) {
    user.value = newUser;
  }

  function logout() {
    token.value = null;
    user.value = null;
  }

  return {
    token,
    user,
    isAuthenticated,
    isAdmin,
    setAuth,
    setUser,
    logout,
  };
});
