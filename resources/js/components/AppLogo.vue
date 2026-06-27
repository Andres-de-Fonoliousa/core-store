<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="flex items-center gap-2.5">
        <div
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-primary/10 border border-primary/30 text-primary"
        >
            <AppLogoIcon class="size-5 fill-current text-primary" />
        </div>
        <div class="grid flex-1 text-right text-sm">
            <span class="mb-0.5 truncate leading-tight font-bold text-white" style="font-family: 'Orbitron', 'Cairo', sans-serif;">
                CoreS
            </span>
        </div>
    </div>
</template>