<template>
  <span
    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
    :class="badgeClass"
  >
    {{ label }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  status: string;
  type?: 'order' | 'fulfillment' | 'deposit';
}>();

const badgeClass = computed(() => {
  switch (props.status) {
    case 'paid':
    case 'fulfilled':
    case 'completed':
    case 'approved':
    case 'active':
      return 'bg-success/10 text-success';
    case 'pending_fulfillment':
    case 'pending':
      return 'bg-warning/10 text-warning';
    case 'failed':
    case 'rejected':
    case 'inactive':
      return 'bg-destructive/10 text-destructive';
    case 'unpaid':
    default:
      return 'bg-muted text-muted-foreground';
  }
});

const label = computed(() => {
  if (props.type === 'deposit') {
    return props.status === 'approved' ? 'Approved' : props.status === 'rejected' ? 'Rejected' : 'Pending';
  }
  return props.status.replace(/_/g, ' ');
});
</script>