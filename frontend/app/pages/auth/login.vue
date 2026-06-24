<template>
  <div class="min-h-screen flex items-center justify-center bg-white dark:bg-slate-950 p-4 transition-colors">
    <div class="max-w-sm w-full space-y-8">
      <div class="text-center">
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100 tracking-tight">Welcome Back</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm">Please enter your details to sign in.</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <BaseInput id="email" label="Email" v-model="email" type="email" icon="pi-envelope" placeholder="Enter Email"
          :error="errors.email" required />

        <BaseInput id="password" label="Password" v-model="password" type="password" icon="pi-lock"
          :error="errors.password" placeholder="Enter Password" required />

        <div v-if="errorMsg"
          class="flex items-center gap-2 text-red-600 dark:text-red-400 text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg border border-red-100 dark:border-red-900/30">
          <i class="pi pi-exclamation-circle text-lg"></i>
          {{ errorMsg }}
        </div>

        <BaseButton type="submit" :is-loading="isLoading" icon="pi-sign-in" loading-text="Signing in..." block>
          Sign in
        </BaseButton>
      </form>

      <p class="text-center text-sm text-slate-600 dark:text-slate-400">
        Don't have an account?
        <NuxtLink to="/auth/register" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium hover:underline">
          Sign up
        </NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">

import { ref } from 'vue'
import { useRouter } from 'vue-router'
import BaseButton from '~/components/ui/BaseButton.vue'
import BaseInput from '~/components/ui/BaseInput.vue'
import { useAuthStore } from '~/stores/auth'
import { useToast } from '~/composables/useToast'

definePageMeta({
  layout: 'guest',
})


const email = ref('')
const password = ref('')
const errorMsg = ref('')
const errors = ref<Record<string, string>>({})
const isLoading = ref(false)

const router = useRouter()
const authStore = useAuthStore()
const api = useApi()
const { addToast } = useToast()

async function handleLogin() {
  isLoading.value = true
  errorMsg.value = ''
  errors.value = {}

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
      addToast('Login successful! Welcome back.', 'success')
      router.push('/')
    } else {
      errorMsg.value = response.message || 'Login failed'
    }
  } catch (err: any) {
    if (err.response?.status === 422 && err.response?._data?.errors) {
      const apiErrors = err.response._data.errors
      for (const key in apiErrors) {
        errors.value[key] = apiErrors[key][0]
      }
    } else if (err.response?._data?.message) {
      errorMsg.value = err.response._data.message
    } else {
      errorMsg.value = 'An error occurred during login. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>
