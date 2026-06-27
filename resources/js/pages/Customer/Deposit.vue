<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { parseApiError } from '@/lib/errors';
import DepositProofUpload from '@/components/DepositProofUpload.vue';
import {
  Wallet, Upload, Send, CheckCircle, AlertTriangle, Banknote,
  Smartphone, ChevronRight, ChevronLeft, Copy, Check,
} from 'lucide-vue-next';

const api = useApi();
const toast = useToast();

// Wizard step
const step = ref(1);

// Payment methods list
const methods = ref<any[]>([]);
const selectedMethod = ref<any>(null);
const selectedAddress = ref<any>(null);

// Form fields
const amount = ref<number | null>(null);
const proof = ref<File | null>(null);
const note = ref('');
const tranId = ref('');
const submitting = ref(false);
const previewUrl = ref('');
const copied = ref(false);

const isLoadingMethods = ref(true);

onMounted(async () => {
  try {
    const { data } = await api.get('/payment-methods');
    methods.value = data.data ?? data ?? [];
  } catch (err) {
    toast.error(parseApiError(err));
  } finally {
    isLoadingMethods.value = false;
  }
});

const currentMethodAddresses = computed(() => {
  if (!selectedMethod.value) return [];
  return selectedMethod.value.addresses ?? [];
});

const isManual = computed(() => selectedMethod.value?.code === 'manual_screenshot');
const isShamCash = computed(() => selectedMethod.value?.code === 'sham_cash');

// Update selectedAddress when method changes
function selectMethod(method: any) {
  selectedMethod.value = method;
  selectedAddress.value = method.addresses?.[0] ?? null;
  step.value = 2;
}

function backToMethod() {
  step.value = 1;
}

async function submitDeposit() {
  if (!amount.value || !selectedMethod.value) {
    toast.error('Please enter an amount and select a payment method.');
    return;
  }

  if (isManual.value && !proof.value) {
    toast.error('Please upload proof of payment.');
    return;
  }

  if (isShamCash.value && !tranId.value) {
    toast.error('Please enter the Sham Cash transaction ID.');
    return;
  }

  submitting.value = true;
  const formData = new FormData();
  formData.append('amount', String(amount.value));
  formData.append('payment_method_id', String(selectedMethod.value.id));
  if (selectedAddress.value) {
    formData.append('payment_address_id', String(selectedAddress.value.id));
  }
  if (isManual.value && proof.value) {
    formData.append('proof', proof.value);
  }
  if (isShamCash.value && tranId.value) {
    formData.append('tran_id', tranId.value);
  }
  if (note.value) formData.append('note', note.value);

  try {
    const { data } = await api.post('/deposits', formData);
    toast.success(isShamCash.value ? 'جاري التحقق من المعاملة...' : 'تم إرسال طلب الإيداع بنجاح!');
    setTimeout(() => router.visit(`/deposit/${data.transaction.id}`), 800);
  } catch (err: any) {
    toast.error(parseApiError(err));
  } finally {
    submitting.value = false;
  }
}

function copyAddressCode(code: string) {
  navigator.clipboard.writeText(code);
  copied.value = true;
  toast.success('تم نسخ العنوان');
  setTimeout(() => copied.value = false, 2000);
}
</script>

<template>
  <Head :title="$t('deposit.title')" />

  <div class="space-y-8 max-w-2xl">
    <!-- Header -->
    <div>
      <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">الرصيد</span>
      <h1 class="text-3xl font-bold tracking-tight mt-1 text-white">{{ $t('deposit.title') }}</h1>
      <p class="text-sm text-white/40 mt-1">اختر طريقة الدفع وأرسل طلب الإيداع.</p>
    </div>

    <!-- Loading -->
    <div v-if="isLoadingMethods" class="bg-white/[0.02] border border-white/5 rounded-2xl p-10 text-center">
      <div class="w-8 h-8 border-2 border-cyan-400/30 border-t-cyan-400 rounded-full animate-spin mx-auto mb-3" />
      <p class="text-sm text-white/40 font-mono">Loading payment methods...</p>
    </div>

    <!-- Step 1: Select Payment Method -->
    <div v-else-if="step === 1" class="space-y-4">
      <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
        <h2 class="text-lg font-semibold text-white mb-5">اختر طريقة الدفع</h2>
        <div class="space-y-3">
          <button
            v-for="method in methods"
            :key="method.id"
            @click="selectMethod(method)"
            class="w-full flex items-center gap-4 p-4 rounded-xl border border-white/5 bg-white/[0.02] hover:bg-white/[0.04] hover:border-cyan-400/30 transition-all text-right group"
          >
            <div class="w-12 h-12 rounded-xl flex items-center justify-center"
              :class="method.code === 'sham_cash' ? 'bg-purple-500/10 border border-purple-400/20' : 'bg-emerald-500/10 border border-emerald-400/20'">
              <component :is="method.code === 'sham_cash' ? Smartphone : Banknote" class="w-6 h-6"
                :class="method.code === 'sham_cash' ? 'text-purple-400' : 'text-emerald-400'" />
            </div>
            <div class="flex-1 text-right">
              <p class="text-sm font-semibold text-white">{{ method.name }}</p>
              <p class="text-xs text-white/40 mt-0.5">{{ method.addresses?.length ?? 0 }} عنوان متاح</p>
            </div>
            <ChevronLeft class="w-5 h-5 text-white/20 group-hover:text-cyan-400 transition-colors" />
          </button>
        </div>
      </div>
    </div>

    <!-- Step 2: Fill Details -->
    <div v-else-if="step === 2 && selectedMethod" class="space-y-6">
      <!-- Method header -->
      <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
        <div class="flex items-center gap-3">
          <button @click="backToMethod" class="text-white/30 hover:text-cyan-400 transition-colors">
            <ChevronRight class="w-5 h-5" />
          </button>
          <div class="w-10 h-10 rounded-xl flex items-center justify-center"
            :class="isShamCash ? 'bg-purple-500/10 border border-purple-400/20' : 'bg-emerald-500/10 border border-emerald-400/20'">
            <component :is="isShamCash ? Smartphone : Banknote" class="w-5 h-5"
              :class="isShamCash ? 'text-purple-400' : 'text-emerald-400'" />
          </div>
          <div>
            <h2 class="text-lg font-semibold text-white">{{ selectedMethod.name }}</h2>
            <p class="text-xs text-white/40 font-mono">{{ isManual ? 'يرجى إرفاق إثبات الدفع' : 'أدخل معرف المعاملة من تطبيق Sham Cash' }}</p>
          </div>
        </div>
      </div>

      <!-- Address selection (if multiple) -->
      <div v-if="currentMethodAddresses.length > 0" class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl">
        <label class="text-xs font-mono text-white/60 uppercase tracking-wider mb-3 block">عنوان الدفع</label>
        <div class="space-y-2">
          <button
            v-for="addr in currentMethodAddresses"
            :key="addr.id"
            @click="selectedAddress = addr"
            class="w-full flex items-center justify-between p-3 rounded-xl border transition-all text-right"
            :class="selectedAddress?.id === addr.id ? 'border-cyan-400/40 bg-cyan-400/5' : 'border-white/5 bg-white/[0.02] hover:border-white/10'"
          >
            <div>
              <p class="text-sm font-medium text-white">{{ addr.label }}</p>
              <p class="text-xs text-white/40 font-mono mt-0.5">
                <template v-if="isManual">
                  {{ addr.meta?.bank_name ?? '' }} {{ addr.meta?.account_number ?? '' }}
                </template>
                <template v-else>
                  {{ addr.meta?.address_code ?? '' }}
                </template>
              </p>
            </div>
            <div v-if="selectedAddress?.id === addr.id" class="w-6 h-6 rounded-full bg-cyan-400/10 border border-cyan-400/30 flex items-center justify-center">
              <CheckCircle class="w-4 h-4 text-cyan-400" />
            </div>
          </button>
        </div>

        <!-- Show selected address details -->
        <div v-if="selectedAddress" class="mt-4 p-4 rounded-xl bg-white/[0.02] border border-white/5">
          <div v-if="isManual">
            <div class="grid grid-cols-2 gap-3 text-xs">
              <div v-if="selectedAddress.meta?.bank_name">
                <span class="text-white/40">البنك:</span>
                <p class="text-white font-medium mt-0.5">{{ selectedAddress.meta.bank_name }}</p>
              </div>
              <div v-if="selectedAddress.meta?.account_holder">
                <span class="text-white/40">اسم الحساب:</span>
                <p class="text-white font-medium mt-0.5">{{ selectedAddress.meta.account_holder }}</p>
              </div>
              <div v-if="selectedAddress.meta?.account_number" class="col-span-2">
                <span class="text-white/40">رقم الحساب:</span>
                <p class="text-white font-mono font-medium mt-0.5" dir="ltr">{{ selectedAddress.meta.account_number }}</p>
              </div>
              <div v-if="selectedAddress.meta?.iban" class="col-span-2">
                <span class="text-white/40">رقم الإيبان (IBAN):</span>
                <p class="text-white font-mono font-medium mt-0.5" dir="ltr">{{ selectedAddress.meta.iban }}</p>
              </div>
            </div>
          </div>
          <div v-else>
            <div class="flex items-center justify-between">
              <div>
                <span class="text-xs text-white/40">عنوان المحفظة:</span>
                <p class="text-white font-mono font-medium mt-0.5 text-sm" dir="ltr">{{ selectedAddress.meta?.address_code ?? '' }}</p>
              </div>
              <button @click="copyAddressCode(selectedAddress.meta?.address_code ?? '')" class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg bg-white/5 border border-white/10 text-cyan-400 hover:bg-white/10 transition-all">
                <component :is="copied ? Check : Copy" class="w-3.5 h-3.5" />
                {{ copied ? 'تم' : 'نسخ' }}
              </button>
            </div>
            <p class="text-xs text-amber-400/70 mt-3 flex items-center gap-1.5">
              <AlertTriangle class="w-3.5 h-3.5" />
              أرسل المبلغ إلى العنوان أعلاه من تطبيق Sham Cash الخاص بك، ثم أدخل رقم المعاملة أدناه.
            </p>
          </div>
        </div>
      </div>

      <!-- Form Card -->
      <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl space-y-5">
        <!-- Amount -->
        <div class="space-y-2">
          <label class="text-xs font-mono text-white/60 uppercase tracking-wider">{{ $t('deposit.amount') }}</label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/30 font-mono text-sm">$</span>
            <input
              type="number"
              min="1"
              v-model.number="amount"
              class="w-full h-12 bg-white/[0.02] border border-white/10 rounded-xl pl-10 pr-4 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.03] transition-all"
              placeholder="0.00"
            />
          </div>
        </div>

        <!-- Sham Cash: Transaction ID -->
        <div v-if="isShamCash" class="space-y-2">
          <label class="text-xs font-mono text-white/60 uppercase tracking-wider">رقم المعاملة (Transaction ID)</label>
          <input
            v-model="tranId"
            type="text"
            class="w-full h-12 bg-white/[0.02] border border-white/10 rounded-xl px-4 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-purple-400/50 focus:bg-white/[0.03] transition-all"
            placeholder="مثال: 292457171"
            dir="ltr"
          />
          <p class="text-[11px] text-white/30 font-mono">أدخل رقم المعاملة الذي يظهر في تطبيق Sham Cash بعد إرسال الدفعة.</p>
        </div>

        <!-- Manual: Proof Upload -->
        <div v-if="isManual" class="space-y-2">
          <label class="text-xs font-mono text-white/60 uppercase tracking-wider">{{ $t('deposit.proof') }}</label>
          <div class="bg-white/[0.02] border border-white/5 rounded-xl p-4 hover:border-white/10 transition-all">
            <DepositProofUpload v-model="proof" @preview="previewUrl = $event" />
          </div>
          <p class="text-[11px] text-white/30 font-mono">صيغ مقبولة: JPG, PNG, PDF — بحد أقصى 5 ميجابايت</p>
        </div>

        <!-- Note -->
        <div class="space-y-2">
          <label class="text-xs font-mono text-white/60 uppercase tracking-wider">{{ $t('deposit.note') }}</label>
          <textarea
            v-model="note"
            rows="4"
            class="w-full bg-white/[0.02] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder:text-white/20 focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.03] transition-all resize-none"
            :placeholder="$t('deposit.note')"
          />
        </div>

        <!-- Submit -->
        <button
          type="button"
          @click="submitDeposit"
          :disabled="submitting"
          class="w-full inline-flex items-center justify-center gap-2 h-12 text-sm font-semibold rounded-xl transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
          :class="isShamCash
            ? 'bg-purple-500 text-white hover:bg-purple-400 hover:shadow-[0_0_25px_-5px_rgba(168,85,247,0.5)]'
            : 'bg-cyan-400 text-black hover:bg-cyan-300 hover:shadow-[0_0_25px_-5px_rgba(34,211,238,0.5)]'"
        >
          <Send v-if="!submitting" class="w-4 h-4" />
          <CheckCircle v-else class="w-4 h-4" />
          {{ submitting ? 'جاري الإرسال...' : isShamCash ? 'تأكيد الإيداع' : $t('deposit.submit') }}
        </button>
      </div>
    </div>

    <!-- Info -->
    <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl flex items-start gap-4">
      <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-400/20 flex items-center justify-center flex-shrink-0">
        <AlertTriangle class="w-5 h-5 text-amber-400" />
      </div>
      <div>
        <h3 class="text-sm font-semibold text-white">تنويه مهم</h3>
        <p class="text-xs text-white/40 mt-1 leading-relaxed">
          <template v-if="isShamCash">
            تأكد من إدخال رقم المعاملة الصحيح من تطبيق Sham Cash. سيتم التحقق من المعاملة آلياً وإضافة الرصيد إلى محفظتك.
          </template>
          <template v-else>
            تأكد من إرفاق إثبات دفع واضح يظهر فيه المبلغ وتاريخ المعاملة. الطلبات غير المرفقة بإثبات صحيح سيتم رفضها تلقائياً.
          </template>
        </p>
      </div>
    </div>
  </div>
</template>
