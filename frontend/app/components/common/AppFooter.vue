<template>
  <footer
    class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 mt-auto"
  >
    <div class="max-w-7xl mx-auto px-4 tablet:px-6 laptop:px-8 py-8">
      <div
        class="flex flex-col tablet:flex-row justify-between items-center gap-6"
      >
        <!-- Logo & Copyright -->
        <div class="flex flex-col items-center tablet:items-start gap-2">
          <NuxtLink to="/" class="flex items-center gap-2">
            <i class="pi pi-wallet text-indigo-600 text-lg"></i>
            <span
              class="font-bold text-lg tracking-tight text-slate-800 dark:text-slate-100"
              >Splitter</span
            >
          </NuxtLink>
          <p class="text-slate-500 text-sm">
            &copy; {{ new Date().getFullYear() }} Splitter. All rights reserved.
          </p>
        </div>

        <!-- Links -->
        <div class="flex flex-wrap justify-center gap-6">
          <NuxtLink
            to="#"
            class="text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors"
          >
            Privacy Policy
          </NuxtLink>
          <NuxtLink
            to="#"
            class="text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors"
          >
            Terms of Service
          </NuxtLink>
          <NuxtLink
            to="#"
            class="text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors"
          >
            Contact Us
          </NuxtLink>
        </div>

        <!-- Social Icons -->
        <div class="flex gap-4">
          <a
            href="#"
            class="text-slate-400 hover:text-indigo-600 transition-colors"
          >
            <i class="pi pi-twitter text-lg"></i>
          </a>
          <a
            href="#"
            class="text-slate-400 hover:text-indigo-600 transition-colors"
          >
            <i class="pi pi-github text-lg"></i>
          </a>
          <a
            href="#"
            class="text-slate-400 hover:text-indigo-600 transition-colors"
          >
            <i class="pi pi-discord text-lg"></i>
          </a>
        </div>
      </div>
    </div>
  </footer>
</template>
