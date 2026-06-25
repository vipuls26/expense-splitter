<script setup lang="ts">
import AppFooter from "~/components/common/AppFooter.vue";
import AppNavbar from "~/components/common/AppNavbar.vue";
</script>

<template>
  <div class="min-h-screen flex flex-col bg-white">
    <AppNavbar />

    <main
      class="flex justify-center mx-auto w-full max-w-7xl px-4 tablet:px-6 laptop:px-8"
    >
      <slot />
    </main>

    <AppFooter />
  </div>
</template>
