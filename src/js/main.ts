// Import Font loader
import WebFont from 'webfontloader';

import { createApp, defineAsyncComponent } from 'vue';

import 'virtual:svg-icons-register';

export default {
  init() {
    // JavaScript to be fired on all pages

    // Load Fonts
    WebFont.load({
            classes: false,
      events: false,
      google: {
        families: ['Roboto:wght@300;400;700'],
        display: 'swap',
        version: 2,
      },
    })

    // Define the selectors and src for dynamic imports
    const scripts: {
      selector: string,
      src: string,
    }[] = [
      {
        selector: '#tofino-alert', // Alert
        src: 'alert',
      },
      {
        selector: '#main-menu', // Main menu
        src: 'menu',
      },
    ];

    // Loop through the scripts and import the src
    scripts.forEach(({ selector, src }) => {
      const el: HTMLElement | null = document.querySelector(selector);

      if (el) {
        import(`./modules/${src}.ts`).then(({ default: script }) => {
          script();
        });
      } else {
        console.warn(`Tofino Theme: Could not find ${selector} for script ${src}.ts.`);
      }
    });

    if (document.getElementById('app')) {
      // Vue
      const app = createApp({
        components: {
          HelloWorld: defineAsyncComponent(() =>
            import('../vue/HelloWorld.vue')
          ),
        },
      });

      console.log('Vue app loaded');

      app.mount('#app');
    }
  },
  finalize() {
    // JavaScript to be fired after init
  },
  loaded() {
    // Javascript to be fired once fully loaded
  },
};
