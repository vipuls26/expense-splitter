import { defineStore } from 'pinia'
// define interface in types folder
import type { User } from '~/types/user'

export const useAuthStore = defineStore('auth', {
   state: () => ({
      user: null as User | null,
      token: null as string | null,
   }),
   getters: {
      // convert value to boolean 
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
      async logout() {
         const api = useApi()

         try {
            await api('/logout', { method: 'POST' })
         } catch (error) {
            console.error(error)
         }

         this.user = null
         this.token = null

         const cookie = useCookie('auth_token')
         cookie.value = null
      },
      // Fetch the authenticated user's details from the backend API
      async fetchUser() {
         if (!this.token) return

         const api = useApi()

         try {
            const response: any = await api('/me', { method: 'GET' })

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

