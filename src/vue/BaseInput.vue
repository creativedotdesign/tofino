<script setup lang="ts">
import { toRef, computed } from 'vue';
import { useField, defineRule } from 'vee-validate';
import { required, email, min } from '@vee-validate/rules';

defineRule('required', required);
defineRule('email', email);
defineRule('min', min);

const props = defineProps<{
  label?: string | undefined;
  name: string;
  rules?: string;
}>();

const nameRef = toRef(props, 'name');

const validationListeners = computed(() => {
  // If the field is valid or have not been validated yet
  // lazy
  if (!errorMessage.value) {
    return {
      blur: handleChange,
      change: handleChange,
      // disable `shouldValidate` to avoid validating on input
      input: (e: unknown) => handleChange(e, false),
    };
  }
  // Aggressive
  return {
    blur: handleChange,
    change: handleChange,
    input: handleChange, // only switched this
  };
});

const {
  value: inputValue,
  errorMessage,
  handleChange,
} = useField(nameRef, props.rules, {
  validateOnValueUpdate: false,
  label: props.label,
});
</script>

<template>
  <label
    v-if="label"
    class="mb-1 block text-sm"
    :class="{ 'text-red-500': errorMessage }"
    :for="name"
  >
    {{ label }} <span v-if="props.rules">*</span>
  </label>
  <div class="relative">
    <input
      :id="name"
      :class="{
        '!border-red-500 placeholder:text-red-500': errorMessage,
        'w-full border border-gray-400 p-4 focus:border-transparent focus:ring-1 focus:ring-blue-800 md:p-3':
          label,
      }"
      v-bind="{
        ...$attrs,
      }"
      :name="name"
      :value="inputValue"
      :aria-describedby="errorMessage ? `${name}-error` : ''"
      :aria-invalid="errorMessage ? true : false"
      v-on="validationListeners"
    />
    <p
      v-if="errorMessage"
      :id="`${name}-error`"
      :data-cy="`${name}-error`"
      class="mt-1 text-sm font-normal text-red-500"
    >
      {{ errorMessage }}
    </p>
  </div>
</template>
