<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Cpu, Zap, Shield, ArrowLeft } from 'lucide-vue-next';

defineOptions({
    layout: {
        title: 'تسجيل الدخول',
        description: 'أدخل بريدك الإلكتروني وكلمة المرور للدخول',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
  <Head title="تسجيل الدخول">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&family=Orbitron:wght@500;600;700;800&display=swap" rel="stylesheet" />
  </Head>

  <div
    dir="rtl"
    class="min-h-screen bg-background text-foreground relative flex items-center justify-center p-4"
    style="font-family: 'Cairo', 'Inter', system-ui, sans-serif;"
  >
    <!-- Background -->
    <div class="fixed inset-0 bg-grid pointer-events-none z-0" />
    <div class="fixed inset-0 pointer-events-none z-0" style="background: radial-gradient(ellipse at 50% 0%, hsl(186 100% 55% / 0.04) 0%, transparent 60%)" />

    <!-- Floating decorative elements -->
    <div class="fixed top-20 left-10 w-32 h-32 border border-primary/5 rounded-full pointer-events-none animate-float opacity-30" />
    <div class="fixed bottom-20 right-10 w-24 h-24 border border-primary/5 rounded-full pointer-events-none animate-float opacity-20" style="animation-delay: 2s" />

    <!-- Main Card -->
    <div class="relative z-10 w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <Link href="/" class="inline-flex items-center gap-2.5 group mb-4">
          <div class="w-10 h-10 rounded-lg flex items-center justify-center border border-primary/30 bg-primary/10 glow-primary">
            <Cpu class="w-5 h-5 text-primary" />
          </div>
          <span class="text-2xl font-bold tracking-wider text-white group-hover:text-primary transition-colors" style="font-family: 'Orbitron', 'Cairo', sans-serif;">
            CoreS
          </span>
        </Link>
        <p class="text-sm text-muted-foreground">منصة المنتجات الرقمية للاعبين</p>
      </div>

      <!-- Status Message -->
      <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-success bg-success/10 border border-success/20 rounded-lg px-4 py-3"
      >
        <span class="inline-flex items-center gap-2">
          <Shield class="w-4 h-4" />
          {{ status }}
        </span>
      </div>

      <!-- Passkey -->
      <PasskeyVerify />

      <!-- Form Card -->
      <div class="glass border border-primary/10 rounded-xl p-6 md:p-8 relative overflow-hidden">
        <!-- Top glow line -->
        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-primary/30 to-transparent" />

        <!-- Corner brackets -->
        <div class="absolute top-3 left-3 w-5 h-5 border-t border-l border-primary/20" />
        <div class="absolute top-3 right-3 w-5 h-5 border-t border-r border-primary/20" />
        <div class="absolute bottom-3 left-3 w-5 h-5 border-b border-l border-primary/20" />
        <div class="absolute bottom-3 right-3 w-5 h-5 border-b border-r border-primary/20" />

        <div class="mb-6">
          <h1 class="text-xl font-bold text-white mb-1" style="font-family: 'Cairo', 'Orbitron', sans-serif;">
            تسجيل الدخول <span class="text-gradient">//</span>
          </h1>
          <p class="text-sm text-muted-foreground">أدخل بياناتك للوصول إلى حسابك</p>
        </div>

        <Form
          v-bind="store.form()"
          :reset-on-success="['password']"
          v-slot="{ errors, processing }"
          class="flex flex-col gap-5"
        >
          <div class="grid gap-5">
            <!-- Email -->
            <div class="grid gap-2">
              <Label for="email" class="text-sm font-medium text-white">
                البريد الإلكتروني
              </Label>
              <div class="relative">
                <Input
                  id="email"
                  type="email"
                  name="email"
                  required
                  autofocus
                  :tabindex="1"
                  autocomplete="email"
                  placeholder="name@example.com"
                  class="w-full px-4 py-3 bg-muted/50 border border-border rounded-lg text-white placeholder:text-muted-foreground focus:border-primary/50 focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all pr-10"
                />
                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>
                </div>
              </div>
              <InputError :message="errors.email" class="text-danger text-xs" />
            </div>

            <!-- Password -->
            <div class="grid gap-2">
              <div class="flex items-center justify-between">
                <Label for="password" class="text-sm font-medium text-white">
                  كلمة المرور
                </Label>
                <TextLink
                  v-if="canResetPassword"
                  :href="request()"
                  class="text-xs text-primary hover:text-primary/80 transition-colors"
                  :tabindex="5"
                >
                  نسيت كلمة المرور؟
                </TextLink>
              </div>
              <PasswordInput
                id="password"
                name="password"
                required
                :tabindex="2"
                autocomplete="current-password"
                placeholder="••••••••"
                class="w-full px-4 py-3 bg-muted/50 border border-border rounded-lg text-white placeholder:text-muted-foreground focus:border-primary/50 focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all"
              />
              <InputError :message="errors.password" class="text-danger text-xs" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
              <Label for="remember" class="flex items-center gap-3 cursor-pointer">
                <Checkbox id="remember" name="remember" :tabindex="3" class="border-border data-[state=checked]:bg-primary data-[state=checked]:border-primary" />
                <span class="text-sm text-muted-foreground">تذكرني</span>
              </Label>
            </div>

            <!-- Submit -->
            <Button
              type="submit"
              class="mt-2 w-full py-3 bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/90 transition-all glow-primary disabled:opacity-60"
              :tabindex="4"
              :disabled="processing"
              data-test="login-button"
            >
              <Spinner v-if="processing" class="mr-2" />
              <span class="flex items-center justify-center gap-2">
                <Zap v-if="!processing" class="w-4 h-4" />
                {{ processing ? 'جاري الدخول...' : 'تسجيل الدخول' }}
              </span>
            </Button>
          </div>

          <!-- Divider -->
          <div class="relative my-2">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-primary/10" />
            </div>
            <div class="relative flex justify-center text-xs">
              <span class="px-3 bg-card text-muted-foreground font-mono">أو</span>
            </div>
          </div>

          <!-- Register Link -->
          <div class="text-center text-sm">
            <span class="text-muted-foreground">ليس لديك حساب؟</span>
            <TextLink
              :href="register()"
              :tabindex="5"
              class="text-primary hover:text-primary/80 font-semibold mr-1 transition-colors"
            >
              إنشاء حساب جديد
            </TextLink>
          </div>
        </Form>
      </div>

      <!-- Back to home -->
      <div class="text-center mt-6">
        <Link href="/" class="inline-flex items-center gap-2 text-xs text-muted-foreground hover:text-primary transition-colors">
          <ArrowLeft class="w-3.5 h-3.5" />
          العودة إلى الرئيسية
        </Link>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Override shadcn input styles for dark theme */
:deep(input) {
  background: hsl(var(--muted) / 0.5) !important;
  border-color: hsl(var(--border)) !important;
  color: hsl(var(--foreground)) !important;
}
:deep(input:focus) {
  border-color: hsl(var(--primary) / 0.5) !important;
  box-shadow: 0 0 0 2px hsl(var(--primary-glow) / 0.15), 0 0 12px hsl(var(--primary-glow) / 0.2) !important;
}
:deep(input::placeholder) {
  color: hsl(var(--muted-foreground)) !important;
}

/* Checkbox override */
:deep([data-state="checked"]) {
  background-color: hsl(var(--primary)) !important;
  border-color: hsl(var(--primary)) !important;
}

/* Button spinner alignment */
:deep(.spinner) {
  margin-left: 0.5rem;
}

/* Float animation */
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}
.animate-float {
  animation: float 6s ease-in-out infinite;
}
</style>