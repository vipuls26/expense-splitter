export default defineNuxtPlugin(async (nuxtApp) => {
  const authStore = useAuthStore()
  const tokenCookie = useCookie('auth_token')

  if (tokenCookie.value) {
    authStore.token = tokenCookie.value as string
    
    // Only fetch if we have a token but no user
    if (!authStore.user) {
      await authStore.fetchUser()
    }
  }
})
