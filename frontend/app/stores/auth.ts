import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useApi } from "~/composables/useApi";
import type { User } from "~/types/user";
import type { ApiResponse } from "~/types/api";

export const useAuthStore = defineStore("auth", () => {
  const user = ref<User | null>(null);
  const token = ref<string | null>(null);
  const isLoading = ref(false);

  const isLoggedIn = computed(() => !!token.value);
  const api = useApi();

  // securely store user and token in state and cookies
  function setAuth(newUser: User, newToken: string) {
    user.value = newUser;
    token.value = newToken;

    const cookie = useCookie<string | null>("auth_token", {
      maxAge: 60 * 60 * 24 * 7,
    }); // 7 days
    cookie.value = newToken;
  }

  // clear user session and remove tokens
  async function logout() {
    return execute(async () => {
      try {
        await api("/logout", { method: "POST" });
      } catch (error) {
        console.error(error);
      }

      user.value = null;
      token.value = null;

      const cookie = useCookie<string | null>("auth_token");
      cookie.value = null;
    });
  }

  // get current user details from the server
  async function fetchUser() {
    if (!token.value) return;

    return execute(async () => {
      try {
        const response = await api<ApiResponse<{ user: User }>>("/me", {
          method: "GET",
        });

        if (response.success) {
          user.value = response.data.user;
        } else {
          await logout();
        }
      } catch (e) {
        await logout();
      }
    });
  }

  // run an async task while managing loading state
  async function execute<T>(
    callback: () => Promise<T> | void,
  ): Promise<T | void> {
    isLoading.value = true;

    try {
      return await callback();
    } finally {
      isLoading.value = false;
    }
  }

  return {
    user,
    token,
    isLoading,
    isLoggedIn,
    setAuth,
    logout,
    fetchUser,
  };
});
