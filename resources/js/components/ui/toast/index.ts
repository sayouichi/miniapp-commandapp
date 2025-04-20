import { cva, type VariantProps } from "class-variance-authority";
import { computed, ref } from "vue";

export const toastVariants = cva(
  "group pointer-events-auto relative flex w-full items-center justify-between space-x-4 overflow-hidden rounded-md border p-6 pr-8 shadow-lg transition-all data-[swipe=cancel]:translate-x-0 data-[swipe=end]:translate-x-[var(--radix-toast-swipe-end-x)] data-[swipe=move]:translate-x-[var(--radix-toast-swipe-move-x)] data-[swipe=move]:transition-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[swipe=end]:animate-out data-[state=closed]:fade-out-80 data-[state=closed]:slide-out-to-right-full data-[state=open]:slide-in-from-top-full data-[state=open]:sm:slide-in-from-bottom-full",
  {
    variants: {
      variant: {
        default: "border bg-background text-foreground",
        destructive:
          "destructive group border-destructive bg-destructive text-destructive-foreground",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
);

export type ToastVariants = VariantProps<typeof toastVariants>;

// Global toast state management
interface ToastData {
  id: number;
  message: string;
  variant?: "default" | "destructive";
  duration?: number;
}

const toasts = ref<ToastData[]>([]);
let idCounter = 0;

export function useToast() {
  const toast = (message: string, options: { variant?: "default" | "destructive"; duration?: number } = {}) => {
    const id = idCounter++;
    const { variant = "default", duration = 3000 } = options;

    toasts.value.push({ id, message, variant, duration });

    setTimeout(() => {
      dismissToast(id);
    }, duration);
  };

  const dismissToast = (id: number) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
  };

  return {
    toast,
    dismissToast,
    toasts: computed(() => toasts.value)
  };
}
