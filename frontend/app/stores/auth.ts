import { defineStore } from "pinia";
import { useApi } from "~/composables/useApi";
import type { User } from "~/types/user";
import type { ApiResponse } from "~/types/api";
import type { LoginPayload, RegisterPayload, AuthResponse } from "~/types/auth";

export const useAuthStore = defineStore("auth", () => {
  const user = ref<User | null>(null);

  const authCookie = useCookie<string | null>("auth_token", { maxAge: 60 * 60 * 24 * 7, }); // 7 days
  const token = ref<string | null>(authCookie.value);

  const isLoading = ref(false);

  const isLoggedIn = computed(() => !!token.value);
  const api = useApi();



  // securely store user and token in state and cookies
  function setAuth(newUser: User, newToken: string) {
    user.value = newUser;
    token.value = newToken;
    authCookie.value = newToken;
  }

  // login function
  async function login(payload: LoginPayload) {
    return execute(async () => {
      const response = await api<ApiResponse<AuthResponse>>("/login", {
        method: "POST",
        body: payload,
      });

      if (response.success) {
        setAuth(response.data.user, response.data.token);
      }

      return response;

    });
  }

  // register function
  async function register(payload: RegisterPayload) {
    return execute(async () => {
      const response = await api<ApiResponse<AuthResponse>>("/register", {
        method: "POST",
        body: payload,
      });

      if (response.success) {
        setAuth(response.data.user, response.data.token);
      }

      return response;

    });
  }

  // clear user session and remove tokens
  async function logout() {
    return execute(async () => {
      try {
        await api("/logout", { method: "POST" });
      } catch (error) {
        console.error("Logout request failed:", error);
      }

      clearAuth();

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

  // clear auth 
  function clearAuth() {
    user.value = null;
    token.value = null;
    authCookie.value = null;
  }

  // run an async task while managing loading state
  async function execute<T>(
    callback: () => Promise<T>,
  ): Promise<T> {
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
    login,
    register,
    logout,
    fetchUser,
  };
});
