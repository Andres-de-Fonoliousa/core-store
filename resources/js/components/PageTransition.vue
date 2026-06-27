<template>
  <Transition
    :key="$page.url"
    appear
    @before-enter="beforeEnter"
    @enter="enter"
    @leave="leave"
    mode="out-in"
  >
    <slot />
  </Transition>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import gsap from 'gsap';

const $page = usePage();

function beforeEnter(el: Element) {
  gsap.set(el, { opacity: 0, y: 20 });
}

function enter(el: Element, done: () => void) {
  gsap.to(el, {
    opacity: 1,
    y: 0,
    duration: 0.4,
    ease: 'power2.out',
    onComplete: done,
  });
}

function leave(el: Element, done: () => void) {
  gsap.to(el, {
    opacity: 0,
    y: -20,
    duration: 0.2,
    ease: 'power2.in',
    onComplete: done,
  });
}
</script>
