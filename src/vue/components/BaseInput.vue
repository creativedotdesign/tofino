<template>
  <label v-if="label" class="block mb-1.5 text-sm" :class="{ 'text-red-500': error }" :for="id">
    {{ label }}
  </label>
  <div class="relative">
    <input
      :id="id"
      class="w-full px-4 py-3 border outline-none focus:outline-none focus:ring-transparent"
      :class="{ 'border-red-500': error }"
      v-bind="{
        ...$attrs,
        onInput: updateValue,
      }"
      :name="id"
      :value="modelValue"
      :aria-describedby="error ? `${id}-error` : null"
      :aria-invalid="error ? true : false"
    />
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits, withDefaults } from 'vue';
import SetupFormComponent from '@/js/SetupFormComponent';
import { ContactFormProps, EmitUpdateValue } from '@/js/interfaceTypes';

const props = withDefaults(defineProps<ContactFormProps>(), {
  label: '',
  id: '',
  error: '',
  modelValue: '',
});

const emits = defineEmits<EmitUpdateValue>();

const { updateValue } = SetupFormComponent(props, emits);
</script>
