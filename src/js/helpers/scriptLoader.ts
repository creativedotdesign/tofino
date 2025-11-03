import { createApp, defineAsyncComponent } from 'vue';
import { createPinia } from 'pinia';
import { Scripts } from '@/js/types/types';

const createVueApp = (src: string, el: HTMLElement) => {
  try {
    createApp({
      components: {
        [src]: defineAsyncComponent(() => import(`@/js/vue/${src}.vue`)),
      },
    })
      .use(createPinia())
      .mount(el);
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
  scripts.forEach(({ selector, src, type }) => {
    const elements: NodeListOf<HTMLElement> = document.querySelectorAll(selector);

    elements.forEach((el) => {
      if (type === 'vue') {
        createVueApp(src, el);
      } else if (type === 'ts') {
        loadTypeScriptModule(src);
      }
    });
  });
};
