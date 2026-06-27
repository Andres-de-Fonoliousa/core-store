<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Cpu, Zap, ArrowLeft, User, Mail, Lock, ShieldCheck } from 'lucide-vue-next';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'إنشاء حساب',
        description: 'أدخل بياناتك لإنشاء حساب جديد',
    },
});
</script>

<template>
  <Head title="إنشاء حساب">
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
    <div class="fixed top-20 right-10 w-32 h-32 border border-primary/5 rounded-full pointer-events-none animate-float opacity-30" />
    <div class="fixed bottom-20 left-10 w-24 h-24 border border-primary/5 rounded-full pointer-events-none animate-float opacity-20" style="animation-delay: 2s" />

    <!-- Main Card -->
    <div class="relative z-10 w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <Link href="/" class="inline-flex items-center gap-2.5 group mb-3">
          <!-- Laravel Logo placeholder -->
          <div class="w-10 h-10 rounded-lg flex items-center justify-center border border-primary/30 bg-primary/10 glow-primary">
            <Cpu class="w-5 h-5 text-primary" />
          </div>
          <span class="text-2xl font-bold tracking-wider text-white group-hover:text-primary transition-colors" style="font-family: 'Orbitron', 'Cairo', sans-serif;">
            CoreS
          </span>
        </Link>
        <p class="text-sm text-muted-foreground">من لاعبين إلى لاعبين</p>
      </div>

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
            إنشاء حساب <span class="text-gradient">//</span>
          </h1>
          <p class="text-sm text-muted-foreground">أدخل بياناتك للانضمام إلى CoreS</p>
        </div>

        <Form
          v-bind="store.form()"
          :reset-on-success="['password', 'password_confirmation']"
          v-slot="{ errors, processing }"
          class="flex flex-col gap-5"
        >
          <div class="grid gap-5">
            <!-- Name -->
            <div class="grid gap-2">
              <Label for="name" class="text-sm font-medium text-white">
                الاسم الكامل
              </Label>
              <div class="relative">
                <Input
                  id="name"
                  type="text"
                  required
                  autofocus
                  :tabindex="1"
                  autocomplete="name"
                  name="name"
                  placeholder="محمد أحمد"
                  class="w-full px-4 py-3 pr-10 bg-muted/50 border border-border rounded-lg text-white placeholder:text-muted-foreground focus:border-primary/50 focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all"
                />
                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                  <User class="w-4 h-4" />
                </div>
              </div>
              <InputError :message="errors.name" class="text-danger text-xs" />
            </div>

            <!-- Email -->
            <div class="grid gap-2">
              <Label for="email" class="text-sm font-medium text-white">
                البريد الإلكتروني
              </Label>
              <div class="relative">
                <Input
                  id="email"
                  type="email"
                  required
                  :tabindex="2"
                  autocomplete="email"
                  name="email"
                  placeholder="name@example.com"
                  class="w-full px-4 py-3 pr-10 bg-muted/50 border border-border rounded-lg text-white placeholder:text-muted-foreground focus:border-primary/50 focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all"
                />
                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                  <Mail class="w-4 h-4" />
                </div>
              </div>
              <InputError :message="errors.email" class="text-danger text-xs" />
            </div>

            <!-- Password -->
            <div class="grid gap-2">
              <Label for="password" class="text-sm font-medium text-white">
                كلمة المرور
              </Label>
              <div class="relative">
                <PasswordInput
                  id="password"
                  required
                  :tabindex="3"
                  autocomplete="new-password"
                  name="password"
                  placeholder="••••••••"
                  :passwordrules="passwordRules"
                  class="w-full px-4 py-3 pr-10 bg-muted/50 border border-border rounded-lg text-white placeholder:text-muted-foreground focus:border-primary/50 focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all"
                />
                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none">
                  <Lock class="w-4 h-4" />
                </div>
              </div>
              <InputError :message="errors.password" class="text-danger text-xs" />
            </div>

            <!-- Password Confirmation -->
            <div class="grid gap-2">
              <Label for="password_confirmation" class="text-sm font-medium text-white">
                تأكيد كلمة المرور
              </Label>
              <div class="relative">
                <PasswordInput
                  id="password_confirmation"
                  required
                  :tabindex="4"
                  autocomplete="new-password"
                  name="password_confirmation"
                  placeholder="••••••••"
                  :passwordrules="passwordRules"
                  class="w-full px-4 py-3 pr-10 bg-muted/50 border border-border rounded-lg text-white placeholder:text-muted-foreground focus:border-primary/50 focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all"
                />
                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none">
                  <ShieldCheck class="w-4 h-4" />
                </div>
              </div>
              <InputError :message="errors.password_confirmation" class="text-danger text-xs" />
            </div>

            <!-- Submit -->
            <Button
              type="submit"
              class="mt-2 w-full py-3 bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/90 transition-all glow-primary disabled:opacity-60"
              tabindex="5"
              :disabled="processing"
              data-test="register-user-button"
            >
              <Spinner v-if="processing" class="mr-2" />
              <span class="flex items-center justify-center gap-2">
                <Zap v-if="!processing" class="w-4 h-4" />
                {{ processing ? 'جاري الإنشاء...' : 'إنشاء حساب' }}
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

          <!-- Login Link -->
          <div class="text-center text-sm">
            <span class="text-muted-foreground">لديك حساب بالفعل؟</span>
            <TextLink
              :href="login()"
              class="text-primary hover:text-primary/80 font-semibold mr-1 transition-colors"
              :tabindex="6"
            >
              تسجيل الدخول
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

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}
.animate-float {
  animation: float 6s ease-in-out infinite;
}
</style>