import { useState } from "#app";

export type ToastType = "success" | "error" | "info" | "warning";

export interface ToastMessage {
  id: string;
  message: string;
  type: ToastType;
}

export const useToast = () => {
  const toasts = useState<ToastMessage[]>("toasts", () => []);

  const addToast = (message: string, type: ToastType = "info") => {
    const id = Math.random().toString(36).substring(2, 9);
    toasts.value.push({ id, message, type });
    setTimeout(() => {
      removeToast(id);
    }, 3000);
  };

  const removeToast = (id: string) => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  };

  return {
    toasts,
    addToast,
    removeToast,
  };
};
