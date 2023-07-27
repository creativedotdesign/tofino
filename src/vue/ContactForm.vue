<script setup lang="ts">
import { ref } from 'vue';
import { useForm, configure } from 'vee-validate';

import BaseInput from '@/vue/BaseInput.vue';
import BaseTextArea from '@/vue/BaseTextArea.vue';

const success = ref(false);
const responseMessage = ref('');
const formVisible = ref(true);

const { handleSubmit, errors, setErrors, isSubmitting } = useForm();

configure({
  generateMessage: (ctx) => {
    const messages = {
      required: `This field is required.`,
      email: `${ctx.field} is not a valid email.`,
    };

    return messages[ctx.rule.name] || 'This field is invalid';
  },
});

const textInputs = [
  {
    name: 'firstName',
    label: 'First Name',
    rules: 'required',
    type: 'text',
  },
  {
    name: 'lastName',
    label: 'Last Name',
    rules: 'required',
    type: 'text',
  },
  {
    name: 'emailAddress',
    label: 'Email',
    rules: 'required|email',
    type: 'email',
  },
];

// Submit Handler
const submit = handleSubmit(async (values) => {
  // Delay the script by 4 seconds for testing purposes
  // await new Promise((resolve) => setTimeout(resolve, 4000));

  try {
    const response = await fetch(tofinoJS.ajaxUrl, {
      method: 'post',
      body: new URLSearchParams({
        action: 'ajax-contact',
        data: JSON.stringify(values),
      }),
    });

    const data = await response.json();

    if (!data.success) {
      success.value = false;

      if (data.type === 'validation') {
        // Assign errors from backend validation
        setErrors(data.extra);
      }
    } else {
      success.value = true;
    }

    responseMessage.value = data.message;
  } catch (error) {
    console.error(error);

    success.value = false;
    responseMessage.value = 'A system error has occured. Please try again later.';
  }
});
</script>

<template>
  <!-- Response Message -->
  <div
    v-if="responseMessage"
    class="text-center text-xl md:text-2xl"
    data-cy="contact-response-message"
  >
    {{ responseMessage }}
  </div>

  <!-- Form -->
  <form
    v-if="formVisible"
    novalidate
    class="flex flex-col md:flex-row md:flex-wrap md:justify-between"
    :class="{ hidden: success }"
    data-cy="contact-form"
    @submit.prevent="submit"
  >
    <!-- First Name -->
    <div v-for="(input, index) in textInputs" :key="index" class="relative mb-6 w-full md:w-[48%]">
      <base-input :name="input.name" :label="input.label" :rules="input.rules" :type="input.type" />
    </div>

    <!-- Message -->
    <div class="relative mb-6 w-full lg:mb-10">
      <base-text-area name="message" label="Message" rules="required"></base-text-area>
    </div>

    <!-- Submit -->
    <button
      type="submit"
      class="self-center md:self-start"
      :disabled="isSubmitting"
      :class="{ 'cursor-wait': isSubmitting }"
    >
      {{ isSubmitting ? 'Sending...' : 'Send' }}
    </button>
  </form>

  <!-- Error Messages -->
  <div v-if="Object.keys(errors).length !== 0" class="mb-8 text-center text-2xl text-red-500">
    A validation error has occured. Please check the form and try again.
  </div>
</template>
