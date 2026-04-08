import { createApp, defineAsyncComponent } from 'vue';
import { createPinia } from 'pinia';
import type { Script } from '@/js/types/types';

const pinia = createPinia();

/**
 * Creates and mounts a Vue application with a dynamically imported component.
 *
 * @param src - The component name used to resolve the Vue SFC from `@/js/vue/{src}.vue`.
 * @param el - The DOM element to mount the Vue application on.
 * @returns void
 */
const createVueApp = (src: string, el: HTMLElement): void => {
  try {
    createApp({
      components: {
        [src]: defineAsyncComponent(() => import(`@/js/vue/${src}.vue`)),
      },
    })
      .use(pinia)
      .mount(el);
  } catch (error) {
    console.error(`Failed to create Vue app for component ${src}:`, error);
  }
};

/**
 * Dynamically imports and executes a TypeScript module by name.
 *
 * @param src - The module name used to resolve the file from `@/js/modules/{src}.ts`.
 * @returns A promise that resolves when the module's default export has been called.
 */
const loadTypeScriptModule = async (src: string): Promise<void> => {
  try {
    const module = await import(`@/js/modules/${src}.ts`);
    module.default();
  } catch (error) {
    console.error(`Failed to load module ${src}:`, error);
  }
};

/**
 * Iterates over a list of script configurations and conditionally loads each one
 * when its associated DOM selector is matched on the page.
 *
 * @param scripts - An array of script configurations containing selector, src, and type.
 * @returns void
 */
export const loadScripts = (scripts: Script[]): void => {
  scripts.forEach(({ selector, src, type }) => {
    const elements = document.querySelectorAll<HTMLElement>(selector);

    if (!elements.length) return;

    if (type === 'vue') {
      elements.forEach((el) => createVueApp(src, el));
    } else if (type === 'ts') {
      loadTypeScriptModule(src);
    }
  });
};
