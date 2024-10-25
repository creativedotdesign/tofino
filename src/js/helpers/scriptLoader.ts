import { createApp, defineAsyncComponent } from 'vue';
import { createPinia } from 'pinia';
import { Scripts } from '@/js/types/types';

export const loadScripts = async (scripts: Scripts) => {
  // Loop through the scripts and import the src
  scripts.forEach(({ selector, src, type }) => {
    const el: HTMLElement | null = document.querySelector(selector);

    if (el) {
      if (type === 'vue') {
        createApp({
          components: {
            [src]: defineAsyncComponent(() => import(`@/js/vue/${src}.vue`)),
          },
        })
          .use(createPinia())
          .mount(el);

        // console.log(`Tofino Theme: Loaded ${selector} for Vue component ${src}.vue.`);
      } else if (type === 'ts') {
        // Dynamically Import Typescript File
        import(`@/js/modules/${src}.ts`).then(({ default: script }) => {
          script();
        });

        // console.log(`Tofino Theme: Loaded ${selector} for script ${src}.ts.`);
      }
    }
  });
};
