import { mount, flushPromises } from '@vue/test-utils';
import ContactForm from '@/vue/ContactForm.vue';
import BaseInput from '@/vue/BaseInput.vue';

describe('Contact Form', () => {
  it('mounts as expected', async () => {
    const wrapper = mount(ContactForm);

    expect(wrapper).toBeDefined();
  });

  it('renders as correctly', async () => {
    const wrapper = mount(ContactForm);

    expect(wrapper.html()).toMatchSnapshot();
  });

  it('show error message if fields are invalid', async () => {
    const wrapper = mount(ContactForm, {
      global: {
        components: {
          BaseInput,
        },
      },
    });

    wrapper.find('input[name="firstName"]').setValue('');
    wrapper.find('input[name="lastName"]').setValue('');
    wrapper.find('input[name="emailAddress"]').setValue('');

    await flushPromises();

    expect(wrapper.find('[data-cy="firstName-error"]').text()).toContain('This field is required.');
    expect(wrapper.find('[data-cy="lastName-error"]').text()).toContain('This field is required.');
    expect(wrapper.find('[data-cy="emailAddress-error"]').text()).toContain(
      'This field is required.'
    );

    wrapper.find('input[name="emailAddress"]').setValue('invalidEmail@example');

    await flushPromises();

    expect(wrapper.find('[data-cy="emailAddress-error"]').text()).toContain(
      'Email is not a valid email.'
    );

    wrapper.find('input[name="emailAddress"]').setValue('validemailaddress@example.com');

    await flushPromises();

    expect(wrapper.find('[data-cy="emailAddress-error"]').exists()).toBe(false);
  });
});
