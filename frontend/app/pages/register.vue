<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-50 p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 space-y-6 border border-slate-100">
      <div class="text-center">
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Create Account</h1>
        <p class="text-slate-500 mt-2">Sign up to get started.</p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <BaseInput
          id="name"
          label="Full Name"
          v-model="name"
          icon="pi-user"
          placeholder="John Doe"
          required
        />

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
        
        <BaseInput
          id="password_confirmation"
          label="Confirm Password"
          v-model="password_confirmation"
          type="password"
          icon="pi-check-circle"
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
          icon="pi-user-plus"
          loading-text="Creating account..."
        >
          Sign up
        </BaseButton>
      </form>

      <p class="text-center text-sm text-slate-600">
        Already have an account?
        <NuxtLink to="/login" class="text-indigo-600 hover:text-indigo-700 font-medium hover:underline">
          Sign in
        </NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '~/stores/auth'

const name = ref('')
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const errorMsg = ref('')
const isLoading = ref(false)

const router = useRouter()
const authStore = useAuthStore()
const api = useApi()

async function handleRegister() {
  isLoading.value = true
  errorMsg.value = ''
  
  try {
    const response: any = await api('/register', {
      method: 'POST',
      body: {
        name: name.value,
        email: email.value,
        password: password.value,
        password_confirmation: password_confirmation.value
      }
    })
    
    if (response.success) {
      authStore.setAuth(response.data.user, response.data.token)
      router.push('/')
    } else {
      errorMsg.value = response.message || 'Registration failed'
    }
  } catch (err: any) {
    if (err.response?._data?.message) {
      errorMsg.value = err.response._data.message
    } else {
      errorMsg.value = 'An error occurred during registration. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>
