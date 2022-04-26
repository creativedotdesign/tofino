import { ContactFormProps, EmitUpdateValue } from '@/js/types';

export default (props: ContactFormProps, emit: EmitUpdateValue) => {
  const updateValue = (event: Event) => {
    const target = event.target as HTMLInputElement;
    let val = target.value;

    if (target.type === 'checkbox') {
      val = target.checked.toString();
    }

    if (target.type === 'radio') {
      val = props.modelValue === target.value ? target.value : '';
    }

    emit('update:modelValue', val);
  };

  return { updateValue };
};
