<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import { useShopStore } from '@/stores/shopStore';
import {
  Store, Package, CreditCard, Check, ChevronRight, ChevronLeft, ArrowRight,
} from 'lucide-vue-next';

const api = useApi();
const toast = useToast();
const shopStore = useShopStore();

const step = ref(1);
const totalSteps = 3;
const submitting = ref(false);

const form = ref({
  name: shopStore.shop?.name ?? '',
  logo_url: shopStore.shop?.logo ?? '',
  brand_color: shopStore.shop?.color ?? '#4F46E5',
  currency: shopStore.shop?.currency ?? 'USD',
});

const nextStep = () => { if (step.value < totalSteps) step.value++; };
const prevStep = () => { if (step.value > 1) step.value--; };

const canProceed = computed(() => {
  if (step.value === 1) return form.value.name.trim().length > 0;
  return true;
});

async function finish() {
  submitting.value = true;
  try {
    await api.post('/admin/onboarding/complete', {
      name: form.value.name,
      logo_url: form.value.logo_url || null,
      brand_color: form.value.brand_color,
      currency: form.value.currency,
    });
    toast.success('Store setup complete!');
    shopStore.hydrate({
      shop: {
        ...shopStore.shop,
        name: form.value.name,
        logo: form.value.logo_url,
        color: form.value.brand_color,
        currency: form.value.currency,
      },
      platform: false,
    });
    router.visit('/admin');
  } catch (e: any) {
    toast.error(e?.response?.data?.message ?? 'Failed to save');
  } finally {
    submitting.value = false;
  }
}

async function skip() {
  try {
    await api.post('/admin/onboarding/dismiss');
    router.visit('/admin');
  } catch (e) {
    router.visit('/admin');
  }
}
</script>

<template>
  <Head title="Setup Your Store" />
  <div class="min-h-screen bg-gray-950 flex items-center justify-center px-4">
    <div class="w-full max-w-2xl">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-500/10 mb-4">
          <Store class="w-8 h-8 text-indigo-400" />
        </div>
        <h1 class="text-3xl font-bold text-white">Set Up Your Store</h1>
        <p class="text-gray-400 mt-2">Complete these quick steps to get started</p>
      </div>

      <div class="flex items-center justify-center gap-2 mb-8">
        <div v-for="i in totalSteps" :key="i" class="flex items-center gap-2">
          <div
            class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-colors"
            :class="i < step ? 'bg-indigo-500 text-white' : i === step ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/50' : 'bg-gray-800 text-gray-500'"
          >
            <Check v-if="i < step" class="w-4 h-4" />
            <span v-else>{{ i }}</span>
          </div>
          <div v-if="i < totalSteps" class="w-12 h-0.5" :class="i < step ? 'bg-indigo-500' : 'bg-gray-800'" />
        </div>
      </div>

      <div class="rounded-xl border border-gray-800 bg-gray-900/50 p-8">
        <!-- Step 1: Store Info -->
        <div v-if="step === 1">
          <h2 class="text-xl font-semibold text-white mb-2">Store Name</h2>
          <p class="text-sm text-gray-400 mb-6">What's your store called?</p>
          <input
            v-model="form.name"
            placeholder="Enter your store name"
            class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 text-lg"
          />
        </div>

        <!-- Step 2: Branding -->
        <div v-if="step === 2">
          <h2 class="text-xl font-semibold text-white mb-2">Brand Colors</h2>
          <p class="text-sm text-gray-400 mb-6">Pick a brand color for your store</p>
          <div class="flex items-center gap-4">
            <input v-model="form.brand_color" type="color" class="w-16 h-16 rounded-lg cursor-pointer bg-transparent border-0" />
            <input
              v-model="form.brand_color"
              placeholder="#4F46E5"
              class="flex-1 bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 font-mono"
            />
          </div>
          <div class="mt-6">
            <label class="text-sm text-gray-400 mb-2 block">Currency</label>
            <select
              v-model="form.currency"
              class="w-full bg-gray-950 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500"
            >
              <option value="USD">USD - US Dollar</option>
              <option value="EUR">EUR - Euro</option>
              <option value="SAR">SAR - Saudi Riyal</option>
              <option value="AED">AED - UAE Dirham</option>
              <option value="EGP">EGP - Egyptian Pound</option>
            </select>
          </div>
        </div>

        <!-- Step 3: Summary -->
        <div v-if="step === 3">
          <h2 class="text-xl font-semibold text-white mb-2">You're All Set!</h2>
          <p class="text-sm text-gray-400 mb-6">Here's a quick summary of your store</p>
          <div class="space-y-4 bg-gray-950 rounded-lg p-4">
            <div class="flex justify-between">
              <span class="text-gray-400">Store Name</span>
              <span class="text-white font-medium">{{ form.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-400">Brand Color</span>
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" :style="{ backgroundColor: form.brand_color }" />
                <span class="text-white font-mono text-sm">{{ form.brand_color }}</span>
              </div>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-400">Currency</span>
              <span class="text-white font-medium">{{ form.currency }}</span>
            </div>
          </div>
          <div class="mt-6 p-4 rounded-lg bg-indigo-500/5 border border-indigo-500/10">
            <p class="text-sm text-indigo-300">
              Next, you can add payment methods, categories, providers, and products from the admin dashboard.
            </p>
          </div>
        </div>

        <!-- Navigation -->
        <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-800">
          <button v-if="step > 1" @click="prevStep"
            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-400 hover:text-white transition-colors"
          >
            <ChevronLeft class="w-4 h-4" /> Back
          </button>
          <div v-else />

          <div class="flex items-center gap-3">
            <button @click="skip"
              class="text-sm text-gray-500 hover:text-gray-300 transition-colors"
            >
              Skip
            </button>
            <button
              v-if="step < totalSteps"
              @click="nextStep"
              :disabled="!canProceed"
              class="flex items-center gap-2 px-6 py-2 bg-indigo-500 hover:bg-indigo-600 disabled:bg-gray-800 disabled:text-gray-500 text-white rounded-lg text-sm font-medium transition-colors"
            >
              Next <ChevronRight class="w-4 h-4" />
            </button>
            <button
              v-else
              @click="finish"
              :disabled="submitting"
              class="flex items-center gap-2 px-6 py-2 bg-emerald-500 hover:bg-emerald-600 disabled:bg-gray-800 disabled:text-gray-500 text-white rounded-lg text-sm font-medium transition-colors"
            >
              <template v-if="submitting">Saving...</template>
              <template v-else>Go to Dashboard <ArrowRight class="w-4 h-4" /></template>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
