<template>
  <div>
    <h1>Hello {{ name }}!</h1>
  </div>
</template>

<script>
import { onMounted, ref } from 'vue';

export default {
  setup() {
    // let name = 'John Doe';

    const name = ref('John Doe v1');

    onMounted(() => {
      console.log('mounted');

      name.value = 'Jane Doe v4';
    });

    return {
      name,
    };
  },
};
</script>
