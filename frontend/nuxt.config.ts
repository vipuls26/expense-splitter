import tailwindcss from "@tailwindcss/vite";


// export nuxt configuration object
// read by nuxt when application start
export default defineNuxtConfig({

  // for constant behaviour of appliction even after framework update
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  css: [
    'primeicons/primeicons.css',
    '~/assets/css/main.css'
  ],
  // register nuxt module 
  modules: [
    '@pinia/nuxt'
  ],
  // store configuration values
  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8000/api'
    }
  },
  vite: {
    plugins: [
      tailwindcss(),
    ],
  },
})
