<script setup lang="ts">
defineProps<{
  count: number;
}>();
</script>

<template>
  <p>Count: {{ count }}</p>
</template>
