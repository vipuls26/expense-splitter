import Echo from "laravel-echo";
import Pusher from "pusher-js";
import { useAuthStore } from "~/stores/auth";
import { watch } from "vue";

declare global {
  interface Window {
    Pusher: any;
    Echo: any;
  }
}

export default defineNuxtPlugin((nuxtApp) => {
  window.Pusher = Pusher;

  const config = useRuntimeConfig();
  const authStore = useAuthStore();

  const echo = new Echo({
    broadcaster: "reverb",
    key: config.public.reverbKey,
    wsHost: config.public.reverbHost,
    wsPort: config.public.reverbPort,
    wssPort: config.public.reverbPort,
    forceTLS: config.public.reverbScheme === "https",
    disableStats: true,
    enabledTransports: ["ws", "wss"],
    authEndpoint: "http://localhost:8000/broadcasting/auth",
    auth: {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    },
  });
  

  window.Echo = echo;

  // Watch for token changes to re-authenticate or re-initialize Echo if needed
  watch(
    () => authStore.token,
    (newToken) => {
      if (newToken) {
        if (echo.options.auth && echo.options.auth.headers) {
          echo.options.auth.headers.Authorization = `Bearer ${newToken}`;
        }
      } else {
        echo.disconnect();
      }
    }
  );

  return {
    provide: {
      echo,
    },
  };
});
