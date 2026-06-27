import { toast as sonnerToast } from 'vue-sonner';

export interface Toast {
  id: string;
  message: string;
  type: 'success' | 'error' | 'warning' | 'info';
  duration?: number;
}

export function useToast() {
  function addToast(message: string, type: Toast['type'] = 'info', duration?: number) {
    const id = Math.random().toString(36).substring(2, 9);
    sonnerToast[type](message, { duration, id });
  }

  function removeToast(id: string) {
    sonnerToast.dismiss(id);
  }

  return {
    addToast,
    removeToast,
    success: (message: string, duration?: number) => sonnerToast.success(message, { duration }),
    error: (message: string, duration?: number) => sonnerToast.error(message, { duration }),
    warning: (message: string, duration?: number) => sonnerToast.warning(message, { duration }),
    info: (message: string, duration?: number) => sonnerToast.info(message, { duration }),
  };
}
