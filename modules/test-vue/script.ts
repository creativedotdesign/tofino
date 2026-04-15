import { createApp } from 'vue';
import App from './vue/App.vue';

interface TestVueProps {
  title: string;
  count?: number;
}

const defaults: TestVueProps = { title: 'Hello World', count: 0 };

/**
 * Mounts the Test Vue module from module-local Vue files.
 *
 * @returns void
 */
const init = (): void => {
  document.querySelectorAll<HTMLElement>('[data-module="test-vue"]').forEach((el) => {
    const raw = el.dataset.props;
    const props: TestVueProps = raw ? { ...defaults, ...JSON.parse(raw) } : defaults;

    createApp(App, props).mount(el);
  });
};

export default init;
