export const useApi = () => {
  const config = useRuntimeConfig();
  const authStore = useAuthStore();

  return $fetch.create({
    baseURL: config.public.apiBase,
    onRequest({ options }) {
      const headers = new Headers(options.headers || {});

      if (authStore.token) {
        headers.set("Authorization", `Bearer ${authStore.token}`);
      }
      headers.set("Accept", "application/json");

      options.headers = headers;
    },
  });
};
