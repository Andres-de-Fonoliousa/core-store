import type { AxiosError } from 'axios';

export function parseApiError(err: unknown): string {
  const error = err as AxiosError<Record<string, any>>;
  if (!error?.response) {
    if (error?.message === 'Network Error') return 'تعذر الاتصال بالخادم';
    return 'حدث خطأ غير متوقع';
  }

  const data = error.response.data;

  if (data?.error && typeof data.error === 'string') {
    if (data.error === 'out of stock') return 'المنتج غير متوفر حالياً';
    return data.error;
  }
  if (data?.message && typeof data.message === 'string') {
    if (data.message === 'out of stock') return 'المنتج غير متوفر حالياً';
    return data.message;
  }

  if (data?.errors) {
    const messages = Object.values(data.errors).flat();
    if (messages.length > 0) return messages.join(' • ');
  }

  const statusMessages: Record<number, string> = {
    401: 'يجب تسجيل الدخول أولاً',
    403: 'ليس لديك صلاحية لهذا الإجراء',
    404: 'المورد المطلوب غير موجود',
    419: 'انتهت الجلسة، يرجى إعادة تسجيل الدخول',
    422: 'البيانات المدخلة غير صحيحة',
    429: 'طلبات كثيرة جداً، يرجى الانتظار',
    500: 'حدث خطأ في الخادم',
    503: 'الخدمة غير متاحة حالياً',
  };

  return statusMessages[error.response.status] || 'حدث خطأ غير متوقع';
}
