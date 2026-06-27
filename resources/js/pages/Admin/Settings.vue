<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import {
  Settings, Percent, Save, AlertCircle,
} from 'lucide-vue-next';

const api = useApi();
const toast = useToast();
const margin = ref(10);
const recalculate = ref(false);
const loading = ref(false);
const saving = ref(false);

async function loadSettings() {
  loading.value = true;
  try {
    const { data } = await api.get('/admin/settings/profit-margin');
    margin.value = data.percent;
  } catch (error) {
    toast.error('Unable to load profit margin.');
  } finally {
    loading.value = false;
  }
}

async function saveSettings() {
  saving.value = true;
  try {
    await api.post('/admin/settings/profit-margin', { percent: margin.value, recalculate_all: recalculate.value });
    toast.success('Settings saved successfully.');
  } catch (error) {
    toast.error('Unable to save settings.');
  } finally {
    saving.value = false;
  }
}

onMounted(loadSettings);
</script>

<template>
  <Head title="Settings" />
  <div class="space-y-8 max-w-3xl">
    <div>
      <span class="text-xs font-mono text-cyan-400 tracking-[0.2em] uppercase">Configuration</span>
      <h1 class="text-3xl font-bold tracking-tight mt-1">Platform Settings</h1>
      <p class="text-sm text-white/40 mt-1">Control global pricing and catalog behavior.</p>
    </div>

    <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl space-y-6">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-400/20 flex items-center justify-center"><Percent class="w-5 h-5 text-cyan-400" /></div>
        <div><h2 class="text-lg font-semibold text-white">Profit Margin</h2><p class="text-xs text-white/40">Applied to auto-priced products.</p></div>
      </div>

      <div class="space-y-4">
        <div class="space-y-2">
          <div class="flex items-center justify-between"><label class="text-xs font-mono text-white/60 uppercase tracking-wider">Margin Percentage</label><span class="text-2xl font-bold text-cyan-400 font-mono">{{ margin }}%</span></div>
          <input v-model.number="margin" type="range" min="0" max="100" class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-cyan-400" />
          <div class="flex items-center justify-between text-[10px] font-mono text-white/30"><span>0%</span><span>50%</span><span>100%</span></div>
        </div>

        <div class="flex items-start gap-3 p-4 bg-white/[0.02] border border-white/5 rounded-xl">
          <input id="recalculate" type="checkbox" v-model="recalculate" class="mt-0.5 h-4 w-4 rounded border-white/20 bg-white/5 text-cyan-400 focus:ring-cyan-400/50" />
          <div><label for="recalculate" class="text-sm text-white font-medium cursor-pointer">Recalculate all product prices</label><p class="text-xs text-white/40 mt-1">Update existing products using the new margin immediately.</p></div>
        </div>

        <button @click="saveSettings" :disabled="saving" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold bg-cyan-400 text-black rounded-xl hover:bg-cyan-300 hover:shadow-[0_0_25px_-5px_rgba(34,211,238,0.5)] transition-all active:scale-95 disabled:opacity-60">
          <Save class="w-4 h-4" /> {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </div>
    </div>

    <div class="bg-white/[0.02] border border-white/5 rounded-2xl p-6 backdrop-blur-xl flex items-start gap-4">
      <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-400/20 flex items-center justify-center flex-shrink-0"><AlertCircle class="w-5 h-5 text-amber-400" /></div>
      <div>
        <h3 class="text-sm font-semibold text-white">Pricing Guidance</h3>
        <p class="text-xs text-white/40 mt-1 leading-relaxed">Current margin: <span class="text-cyan-400 font-mono font-semibold">{{ loading ? '...' : `${margin}%` }}</span>. Formula: <span class="font-mono text-white/60 bg-white/5 px-2 py-1 rounded border border-white/5 inline-block mt-1">selling_price = cost_price × (1 + margin/100)</span></p>
      </div>
    </div>
  </div>
</template>