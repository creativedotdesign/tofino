<script setup lang="ts">
import CounterDisplay from './components/CounterDisplay.vue';

defineProps<{
  title: string;
  count?: number;
}>();
</script>

<template>
  <div class="rounded border border-gray-200 p-4">
    <h2 class="text-lg font-semibold">{{ title }}</h2>
    <CounterDisplay :count="count ?? 0" />
  </div>
</template>
