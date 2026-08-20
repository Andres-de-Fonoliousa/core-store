<script setup lang="ts">
import AuthenticatedLayout from './AuthenticatedLayout.vue';
import SettingsLayoutInner from './settings/Layout.vue';
</script>

<template>
  <AuthenticatedLayout>
    <SettingsLayoutInner>
      <slot />
    </SettingsLayoutInner>
  </AuthenticatedLayout>
</template>
