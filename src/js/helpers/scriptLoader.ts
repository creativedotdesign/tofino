import { createApp, defineAsyncComponent } from 'vue';
import { createPinia } from 'pinia';
import { Scripts } from '@/js/types/types';

const createVueApp = async (src: string, el: HTMLElement) => {
  try {
    const Component = defineAsyncComponent(() => import(`@/js/vue/${src}.vue`));

    createApp(Component).use(createPinia()).mount(el);
  } catch (error) {
    console.error(`Failed to create Vue app for component ${src}:`, error);
  }
};

const loadTypeScriptModule = async (src: string) => {
  try {
    const module = await import(`@/js/modules/${src}.ts`);

    module.default();
  } catch (error) {
    console.error(`Failed to load module ${src}:`, error);
  }
};

export const loadScripts = async (scripts: Scripts) => {
  const promises = scripts.map(async ({ selector, src, type }) => {
    const el: HTMLElement | null = document.querySelector(selector);

    if (el) {
      if (type === 'vue') {
        await createVueApp(src, el);
      } else if (type === 'ts') {
        await loadTypeScriptModule(src);
      }
    }
  });

  await Promise.all(promises);
};
