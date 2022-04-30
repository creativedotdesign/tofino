<script setup lang="ts">
import { withDefaults } from 'vue';
import SetupFormComponent from '@/js/SetupFormComponent';
import { ContactFormProps, EmitUpdateValue } from '@/js/types';

const props = withDefaults(defineProps<ContactFormProps>(), {
  label: '',
  id: '',
  error: '',
  modelValue: '',
});

const emits = defineEmits<EmitUpdateValue>();

const { updateValue } = SetupFormComponent(props, emits);
</script>

<template>
  <label v-if="label" class="mb-1.5 block" :class="{ 'text-red-500': error }" :for="id">
    {{ label }}
  </label>
  <div class="relative">
    <textarea
      :id="id"
      class="w-full px-4 py-3 outline-none focus:outline-none focus:ring-transparent"
      :class="{ 'border-red-500': error }"
      rows="9"
      v-bind="{
        ...$attrs,
        onInput: updateValue,
      }"
      :name="id"
      :value="modelValue"
      :aria-describedby="error ? `${id}-error` : null"
      :aria-invalid="error ? true : false"
    >
    </textarea>
  </div>
</template>
