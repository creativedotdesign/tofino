// Menu
import Menu from './modules/menu';

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
    });

    // Alert
    if (document.getElementById('tofino-notification')) {
      import('./modules/alert').then((Module) => {
        Module.default();
      });
    }

    // Menu
    if (document.getElementById('main-menu')) {
      Menu();
    }

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
