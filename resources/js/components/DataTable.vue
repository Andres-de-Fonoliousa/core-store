<script setup lang="ts" generic="T extends Record<string, any>">
import { computed } from 'vue';
import { ChevronLeft, ChevronRight, Search, X } from 'lucide-vue-next';

const props = defineProps<{
  data: T[];
  columns: { key: string; label: string; width?: string; align?: 'left' | 'right' | 'center' }[];
  loading?: boolean;
  searchable?: boolean;
  searchKeys?: string[];
  paginated?: boolean;
  page?: number;
  lastPage?: number;
  total?: number;
}>();

const emit = defineEmits<{
  (e: 'search', query: string): void;
  (e: 'page', page: number): void;
}>();

const searchQuery = defineModel<string>('searchQuery', { default: '' });

const hasData = computed(() => props.data.length > 0);
</script>

<template>
  <div class="space-y-4">
    <!-- Search -->
    <div v-if="searchable" class="relative">
      <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-white/30" />
      <input
        v-model="searchQuery"
        @input="emit('search', searchQuery)"
        type="text"
        placeholder="بحث..."
        class="w-full h-11 pl-12 pr-4 bg-white/[0.02] border border-white/10 rounded-xl text-sm text-white placeholder:text-white/25 focus:outline-none focus:border-cyan-400/50 transition-all"
      />
      <button v-if="searchQuery" @click="searchQuery = ''; emit('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-red-400 transition-colors">
        <X class="w-3.5 h-3.5" />
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden backdrop-blur-xl">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-white/5 text-left text-xs font-mono uppercase tracking-[0.12em] text-white/40">
            <tr>
              <th
                v-for="col in columns"
                :key="col.key"
                class="px-5 py-4"
                :class="[col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left', col.width]"
              >
                {{ col.label }}
              </th>
            </tr>
          </thead>
          <tbody>
            <!-- Loading -->
            <tr v-if="loading">
              <td :colspan="columns.length" class="px-5 py-10 text-center text-white/30 font-mono text-sm">
                <div class="flex items-center justify-center gap-2">
                  <div class="w-4 h-4 border-2 border-cyan-400/30 border-t-cyan-400 rounded-full animate-spin" />
                  Loading...
                </div>
              </td>
            </tr>

            <!-- Data -->
            <slot v-else-if="hasData" name="rows" :items="data" />

            <!-- Empty -->
            <tr v-else>
              <td :colspan="columns.length" class="px-5 py-16 text-center">
                <slot name="empty">
                  <div class="flex flex-col items-center justify-center text-white/30">
                    <p class="text-sm font-medium">لا توجد بيانات</p>
                  </div>
                </slot>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="paginated && (lastPage ?? 1) > 1" class="flex items-center justify-center gap-3">
      <button
        @click="emit('page', (page ?? 1) - 1)"
        :disabled="(page ?? 1) <= 1"
        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm rounded-xl border border-white/10 bg-white/[0.02] hover:border-cyan-400/30 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
      >
        <ChevronRight class="w-4 h-4" /> السابق
      </button>
      <div class="flex items-center gap-2">
        <button
          v-for="p in (lastPage ?? 1)"
          :key="p"
          @click="emit('page', p)"
          class="w-10 h-10 text-sm rounded-xl border transition-all"
          :class="(page ?? 1) === p ? 'bg-cyan-400/10 text-cyan-400 border-cyan-400/30 font-bold' : 'bg-white/[0.02] text-white/60 border-white/10 hover:border-cyan-400/20'"
        >
          {{ p }}
        </button>
      </div>
      <button
        @click="emit('page', (page ?? 1) + 1)"
        :disabled="(page ?? 1) >= (lastPage ?? 1)"
        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm rounded-xl border border-white/10 bg-white/[0.02] hover:border-cyan-400/30 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
      >
        التالي <ChevronLeft class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>