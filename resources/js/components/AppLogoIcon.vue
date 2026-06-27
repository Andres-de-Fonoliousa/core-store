<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        :class="className"
        v-bind="$attrs"
    >
        <!-- Hexagon frame -->
        <path d="M12 2l8.66 5v10L12 22l-8.66-5V7L12 2z" />
        <!-- Inner circuit lines -->
        <path d="M12 8v8" />
        <path d="M8 10l4-2 4 2" />
        <path d="M8 14l4 2 4-2" />
        <!-- Center dot -->
        <circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none" />
    </svg>
</template>