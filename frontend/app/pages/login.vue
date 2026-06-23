<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-50 p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 space-y-6 border border-slate-100">
      <div class="text-center">
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Welcome Back</h1>
        <p class="text-slate-500 mt-2">Please enter your details to sign in.</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <BaseInput
          id="email"
          label="Email"
          v-model="email"
          type="email"
          icon="pi-envelope"
          placeholder="you@example.com"
          required
        />

        <BaseInput
          id="password"
          label="Password"
          v-model="password"
          type="password"
          icon="pi-lock"
          placeholder="••••••••"
          required
        />

        <div v-if="errorMsg" class="flex items-center gap-2 text-red-600 text-sm bg-red-50 p-3 rounded-lg border border-red-100">
          <i class="pi pi-exclamation-circle text-lg"></i>
          {{ errorMsg }}
        </div>

        <BaseButton
          type="submit"
          :is-loading="isLoading"
          icon="pi-sign-in"
          loading-text="Signing in..."
        >
          Sign in
        </BaseButton>
      </form>

      <p class="text-center text-sm text-slate-600">
        Don't have an account?
        <NuxtLink to="/register" class="text-indigo-600 hover:text-indigo-700 font-medium hover:underline">
          Sign up
        </NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '~/stores/auth'

const email = ref('')
const password = ref('')
const errorMsg = ref('')
const isLoading = ref(false)

const router = useRouter()
const authStore = useAuthStore()
const api = useApi()

async function handleLogin() {
  isLoading.value = true
  errorMsg.value = ''
  
  try {
    const response: any = await api('/login', {
      method: 'POST',
      body: {
        email: email.value,
        password: password.value,
      }
    })
    
    if (response.success) {
      authStore.setAuth(response.data.user, response.data.token)
      router.push('/')
    } else {
      errorMsg.value = response.message || 'Login failed'
    }
  } catch (err: any) {
    if (err.response?._data?.message) {
      errorMsg.value = err.response._data.message
    } else {
      errorMsg.value = 'An error occurred during login. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>
