<script setup lang="ts">
import BaseInput from '@/vue/components/BaseInput.vue';
import BaseTextArea from '@/vue/components/BaseTextArea.vue';

import { ref } from 'vue';
import { useField, useForm } from 'vee-validate';
import { object, string } from 'yup';

const success = ref<boolean>(false);
const responseMessage = ref<string>('');
const form = ref<HTMLElement>();

const validationSchema = object({
  firstName: string().required().label('First Name'),
  lastName: string().required().label('Last Name'),
  email: string().required().email().label('Email Address'),
  phone: string().label('Phone Number'),
  message: string().required().label('Message'),
});

const { handleSubmit, errors, setErrors } = useForm({
  validationSchema,
});

const { value: firstName } = useField('firstName');
const { value: lastName } = useField('lastName');
const { value: email } = useField('email');
const { value: phone } = useField('phone');
const { value: message } = useField('message');

// Submit Handler
const submit = handleSubmit(async (values) => {
  try {
    const response = await fetch(tofinoJS.ajaxUrl, {
      method: 'post',
      body: new URLSearchParams({
        action: 'ajax-contact',
        data: JSON.stringify(values),
      }),
    });

    console.log(form);

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
  <div v-if="responseMessage" class="text-xl text-center md:text-2xl">
    {{ responseMessage }}
  </div>

  <!-- Error Messages -->
  <ul v-if="Object.keys(errors).length !== 0" class="mb-8 text-2xl text-center text-red-500">
    <li v-for="error in errors" :key="error">{{ error }}</li>
  </ul>

  <!-- Form -->
  <form
    ref="form"
    class="flex flex-col md:flex-row md:flex-wrap md:justify-between"
    :class="{ hidden: success }"
    @submit.prevent="submit"
  >
    <!-- First Name -->
    <div class="relative mb-6 w-full md:w-[48%]">
      <base-input
        id="contact-first-name"
        v-model="firstName"
        label="First Name"
        type="text"
        :error="errors.firstName"
      ></base-input>
    </div>

    <!-- Last Name -->
    <div class="relative mb-6 w-full md:w-[48%]">
      <base-input
        id="contact-last-name"
        v-model="lastName"
        label="Last Name"
        type="text"
        :error="errors.lastName"
      ></base-input>
    </div>

    <!-- Email -->
    <div class="relative mb-6 w-full md:w-[48%]">
      <base-input
        id="contact-email"
        v-model="email"
        label="Email"
        type="email"
        :error="errors.email"
      ></base-input>
    </div>

    <!-- Phone -->
    <div class="relative mb-6 w-full md:w-[48%]">
      <base-input
        id="contact-phone"
        v-model="phone"
        label="Phone"
        type="text"
        :error="errors.phone"
      ></base-input>
    </div>

    <!-- Message -->
    <div class="relative w-full mb-6 lg:mb-10">
      <base-text-area
        id="contact-message"
        v-model="message"
        label="Message"
        :error="errors.message"
      ></base-text-area>
    </div>

    <!-- Submit -->
    <button type="submit" class="self-center md:self-start">Send</button>
  </form>
</template>
