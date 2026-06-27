<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import {
  ArrowRight, Clock, CheckCircle, XCircle, Image,
  RefreshCw, Wallet, ArrowLeft, Copy, Check, Smartphone, Banknote,
} from 'lucide-vue-next';

const props = defineProps<{ id: number }>();
const api = useApi();
const toast = useToast();

const deposit = ref<any>(null);
const loading = ref(true);
const showPhoto = ref(false);
const copied = ref(false);

const statusMeta: Record<string, { color: string; bg: string; border: string; icon: any; label: string }> = {
  pending:   { color: 'text-amber-400', bg: 'bg-amber-500/10', border: 'border-amber-400/20', icon: Clock, label: 'قيد المراجعة' },
  completed: { color: 'text-emerald-400', bg: 'bg-emerald-500/10', border: 'border-emerald-400/20', icon: CheckCircle, label: 'تمت الموافقة' },
  rejected:  { color: 'text-red-400', bg: 'bg-red-500/10', border: 'border-red-400/20', icon: XCircle, label: 'مرفوض' },
};

async function loadDeposit() {
  loading.value = true;
  try {
    const { data } = await api.get(`/deposits/${props.id}`);
    deposit.value = data.data ?? data;
  } catch (error) {
    toast.error(parseApiError(error));
  } finally {
    loading.value = false;
  }
}

function copyId() {
  navigator.clipboard.writeText(String(deposit.value.id));
  copied.value = true;
  toast.success('تم نسخ الرقم');
  setTimeout(() => copied.value = false, 2000);
}

onMounted(loadDeposit);
</script>

<template>
  <Head title="تفاصيل الإيداع" />

  <div dir="rtl" class="min-h-screen bg-[#050507] text-white relative overflow-x-hidden" style="font-family: 'Cairo', 'Space Grotesk', sans-serif;">
    <!-- Ambient -->
    <div class="fixed inset-0 pointer-events-none z-0">
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#0f0f11_1px,transparent_1px),linear-gradient(to_bottom,#0f0f11_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]" />
      <div class="absolute top-[-10%] left-1/2 -translate-x-1/2 w-[80%] h-[40%] bg-cyan-500/10 blur-[120px] rounded-full" />
    </div>

    <div class="relative z-10 max-w-2xl mx-auto px-4 sm:px-6 pt-10 pb-20">
      <!-- Breadcrumb -->
      <div class="flex items-center gap-2 mb-8">
        <Link href="/dashboard" class="text-xs text-white/40 hover:text-cyan-400 transition-colors flex items-center gap-1">
          <ArrowRight class="w-3 h-3" /> لوحة التحكم
        </Link>
        <span class="text-white/20">/</span>
        <span class="text-xs text-cyan-400 font-mono">#{{ id }}</span>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-6">
        <div class="h-8 bg-white/5 rounded-lg w-1/3 animate-pulse" />
        <div class="h-40 bg-white/[0.02] border border-white/5 rounded-2xl animate-pulse" />
        <div class="h-60 bg-white/[0.02] border border-white/5 rounded-2xl animate-pulse" />
      </div>

      <div v-else-if="!deposit" class="text-center py-20">
        <XCircle class="w-10 h-10 text-white/20 mx-auto mb-3" />
        <p class="text-white/40">لم يتم العثور على الإيداع</p>
      </div>

      <div v-else class="space-y-6">
        <!-- Status Card -->
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl relative overflow-hidden">
          <div class="absolute top-0 left-0 w-full h-1" :class="statusMeta[deposit.status]?.bg.replace('/10', '/40') ?? 'bg-white/10'" />
          <div class="flex items-start justify-between">
            <div>
              <div class="flex items-center gap-2 mb-3">
                <span class="text-xs font-mono text-white/40 tracking-wider">رقم الإيداع</span>
                <button @click="copyId" class="text-white/30 hover:text-cyan-400 transition-colors">
                  <component :is="copied ? Check : Copy" class="w-3 h-3" />
                </button>
              </div>
              <h1 class="text-2xl font-bold font-mono tracking-tight">#{{ deposit.id }}</h1>
              <p class="text-sm text-white/40 mt-1">{{ new Date(deposit.created_at).toLocaleDateString('ar-SA', { dateStyle: 'full' }) }}</p>
            </div>
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border backdrop-blur-sm" :class="[
              statusMeta[deposit.status]?.bg,
              statusMeta[deposit.status]?.border,
              statusMeta[deposit.status]?.color
            ]">
              <component :is="statusMeta[deposit.status]?.icon" class="w-4 h-4" />
              <span class="text-xs font-semibold">{{ statusMeta[deposit.status]?.label }}</span>
            </div>
          </div>

          <div class="mt-6 grid grid-cols-2 gap-4">
            <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4">
              <p class="text-[10px] font-mono text-white/40 uppercase tracking-wider mb-1">المبلغ</p>
              <p class="text-xl font-bold font-mono text-cyan-400">${{ Number(deposit.amount).toFixed(2) }}</p>
            </div>
            <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4">
              <p class="text-[10px] font-mono text-white/40 uppercase tracking-wider mb-1">الرصيد بعد الإيداع</p>
              <p class="text-xl font-bold font-mono text-white">${{ Number(deposit.balance_after ?? 0).toFixed(2) }}</p>
            </div>
          </div>

          <!-- Payment Method -->
          <div v-if="deposit.payment_method" class="mt-4 bg-white/[0.02] border border-white/5 rounded-xl p-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center"
              :class="deposit.payment_method.code === 'sham_cash' ? 'bg-purple-500/10 border border-purple-400/20' : 'bg-emerald-500/10 border border-emerald-400/20'">
              <component :is="deposit.payment_method.code === 'sham_cash' ? Smartphone : Banknote" class="w-4 h-4"
                :class="deposit.payment_method.code === 'sham_cash' ? 'text-purple-400' : 'text-emerald-400'" />
            </div>
            <div>
              <p class="text-[10px] font-mono text-white/40 uppercase tracking-wider mb-0.5">طريقة الدفع</p>
              <p class="text-sm font-medium text-white">{{ deposit.payment_method.name }}</p>
              <p v-if="deposit.payment_id" class="text-xs font-mono text-white/40 mt-0.5">TX: {{ deposit.payment_id }}</p>
            </div>
          </div>
        </div>

        <!-- Proof Image -->
        <div v-if="deposit.proof_url" class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden backdrop-blur-xl">
          <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Image class="w-4 h-4 text-cyan-400" />
              <span class="text-sm font-medium">إيصال الدفع</span>
            </div>
            <button @click="showPhoto = !showPhoto" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">
              {{ showPhoto ? 'إخفاء' : 'عرض' }}
            </button>
          </div>
          <div v-show="showPhoto" class="p-5">
            <img :src="deposit.proof_url" alt="إيصال الدفع" class="w-full rounded-xl border border-white/5" />
          </div>
        </div>

        <!-- Note -->
        <div v-if="deposit.note" class="bg-white/[0.02] border border-white/5 rounded-2xl p-5 backdrop-blur-xl">
          <p class="text-[10px] font-mono text-white/40 uppercase tracking-wider mb-2">ملاحظة</p>
          <p class="text-sm text-white/70 leading-relaxed">{{ deposit.note }}</p>
        </div>

        <!-- Timeline -->
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
          <h3 class="text-sm font-semibold mb-5 flex items-center gap-2">
            <Clock class="w-4 h-4 text-cyan-400" /> سجل الحالة
          </h3>
          <div class="relative space-y-6">
            <div class="absolute right-[7px] top-2 bottom-2 w-px bg-white/10" />
            <!-- Submitted -->
            <div class="relative flex items-start gap-4">
              <div class="w-3.5 h-3.5 rounded-full bg-cyan-400 border-4 border-[#050507] z-10 mt-1" />
              <div>
                <p class="text-sm font-medium text-white">تم إرسال الطلب</p>
                <p class="text-xs text-white/40 font-mono mt-0.5">{{ new Date(deposit.created_at).toLocaleString('ar-SA') }}</p>
              </div>
            </div>
            <!-- Reviewed -->
            <div v-if="deposit.status !== 'pending'" class="relative flex items-start gap-4">
              <div class="w-3.5 h-3.5 rounded-full border-4 border-[#050507] z-10 mt-1" :class="deposit.status === 'completed' ? 'bg-emerald-400' : 'bg-red-400'" />
              <div>
                <p class="text-sm font-medium text-white">
                  {{ deposit.status === 'completed' ? 'تمت الموافقة على الإيداع' : 'تم رفض الإيداع' }}
                </p>
                <p class="text-xs text-white/40 font-mono mt-0.5">
                  {{ deposit.updated_at ? new Date(deposit.updated_at).toLocaleString('ar-SA') : '—' }}
                </p>
                <p v-if="deposit.admin_note" class="text-xs text-white/50 mt-1 bg-white/5 rounded-lg p-2 border border-white/5">
                  {{ deposit.admin_note }}
                </p>
              </div>
            </div>
            <div v-else class="relative flex items-start gap-4">
              <div class="w-3.5 h-3.5 rounded-full bg-white/20 border-4 border-[#050507] z-10 mt-1 animate-pulse" />
              <div>
                <p class="text-sm font-medium text-white/60">قيد المراجعة من الإدارة</p>
                <p class="text-xs text-white/30 font-mono mt-0.5">سيتم إشعارك عند التحديث</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
          <Link href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all">
            <ArrowLeft class="w-3.5 h-3.5" /> لوحة التحكم
          </Link>
          <button @click="loadDeposit" class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold bg-white/[0.02] border border-white/10 rounded-xl hover:border-cyan-400/30 hover:text-cyan-400 transition-all">
            <RefreshCw class="w-3.5 h-3.5" /> تحديث
          </button>
        </div>
      </div>
    </div>
  </div>
</template>