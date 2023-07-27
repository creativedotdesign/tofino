import { mount, flushPromises } from '@vue/test-utils';
import BaseInput from '@/vue/BaseInput.vue';

describe('BaseInput', () => {
  it('mounts as expected', async () => {
    const wrapper = mount(BaseInput, {
      props: {
        name: 'Test',
      },
    });

    expect(wrapper).toBeDefined();
  });

  it('renders as correctly', async () => {
    const wrapper = mount(BaseInput, {
      props: {
        name: 'Test',
      },
    });

    expect(wrapper.html()).toMatchSnapshot();
  });

  it('renders the with a label if label prop is provided', () => {
    const wrapper = mount(BaseInput, {
      props: {
        label: 'Username',
        name: 'username',
      },
    });

    // Assert the existence of the label in the rendered template
    expect(wrapper.find('label').text()).toBe('Username');
  });

  it('does not render a label if label prop is not provided', () => {
    const wrapper = mount(BaseInput, {
      props: {
        name: 'username',
      },
    });

    // Assert the absence of the label in the rendered template
    expect(wrapper.find('label').exists()).toBe(false);
  });

  it('applies the appropriate classes field is invalid', async () => {
    const wrapper = mount(BaseInput, {
      props: {
        label: 'Email',
        name: 'email',
        rules: 'required|email',
      },
    });

    wrapper.find('input').setValue('invalidEmail@example');

    await flushPromises();

    // Assert the presence of the error styles in the rendered template
    expect(wrapper.find('input').classes()).toContain('!border-red-500');
    expect(wrapper.find('input').classes()).toContain('placeholder:text-red-500');
  });

  it('applies the appropriate classes when errorMessage is not present', () => {
    const wrapper = mount(BaseInput, {
      props: {
        label: 'Password',
        name: 'password',
      },
      data() {
        return {
          errorMessage: '',
        };
      },
    });

    // Assert the absence of the error styles in the rendered template
    expect(wrapper.find('input').classes()).not.toContain('!border-red-500');
    expect(wrapper.find('input').classes()).not.toContain('placeholder:text-red-500');
  });

  it('renders the input with the correct "name" attribute', () => {
    const wrapper = mount(BaseInput, {
      props: {
        name: 'username',
      },
    });

    // Assert the "name" attribute of the input element
    expect(wrapper.find('input').attributes('name')).toBe('username');
  });

  it('emits the correct events when interacting with the input', async () => {
    const wrapper = mount(BaseInput, {
      props: {
        name: 'username',
      },
    });

    // Simulate user input and blur events on the input element
    await wrapper.find('input').setValue('john_doe');
    // await wrapper.find('input').trigger('blur');

    await flushPromises();

    // Assert emitted events
    expect(wrapper.emitted().change).toHaveLength(1);
    // expect(wrapper.emitted().blur).toHaveLength(1);
  });
});
