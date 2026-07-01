export default defineNuxtPlugin(async (nuxtApp) => {
  const authStore = useAuthStore();

  if (authStore.token && !authStore.user) {
    await authStore.fetchUser();
  }
});
