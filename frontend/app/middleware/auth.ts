export default defineNuxtRouteMiddleware((to) => {
    const authStore = useAuthStore()

    if (!authStore.isLoggedIn && to.path !== '/auth/login' && to.path !== '/auth/register') {
        return navigateTo('/auth/login')
    }
})