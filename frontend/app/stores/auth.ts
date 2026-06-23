import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as any,
    token: null as string | null,
  }),
  getters: {
    isLoggedIn: (state) => !!state.token,
  },
  actions: {
    // Set the user authentication state and securely save the token in a cookie
    setAuth(user: any, token: string) {
      this.user = user
      this.token = token
      
      const cookie = useCookie('auth_token', { maxAge: 60 * 60 * 24 * 7 }) // 7 days
      cookie.value = token
    },
    // Clear the authentication state and remove the token cookie
    logout() {
      this.user = null
      this.token = null
      
      const cookie = useCookie('auth_token')
      cookie.value = null
    },
    // Fetch the authenticated user's details from the backend API
    async fetchUser() {
      if (!this.token) return
      
      try {
        const response: any = await $fetch('http://localhost:8000/api/me', { 
          method: 'GET',
          headers: {
            Authorization: `Bearer ${this.token}`,
            Accept: 'application/json'
          }
        })
        
        if (response.success) {
          this.user = response.data.user
        } else {
          this.logout()
        }
      } catch (e) {
        this.logout()
      }
    }
  }
})

