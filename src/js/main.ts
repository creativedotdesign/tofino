// Import Font loader
import * as WebFont from 'webfontloader';
import { createApp, defineAsyncComponent } from 'vue';
import { WebFontInterface } from '@/js/types';
import { DefaultApolloClient } from '@vue/apollo-composable';
import { ApolloClient, createHttpLink, InMemoryCache } from '@apollo/client/core';
import { createPinia } from 'pinia';
import 'virtual:svg-icons-register';

// HTTP connection to the API
const httpLink = createHttpLink({
  // You should use an absolute URL here
  uri: '/graphql',
});

// Cache implementation
const cache = new InMemoryCache();

// Create the apollo client
const apolloClient = new ApolloClient({
  link: httpLink,
  cache,
});

export default {
  init() {
    // JavaScript to be fired on all pages

    // Config for WebFontLoader
    const fontConfig: WebFontInterface = {
      classes: false,
      events: false,
      google: {
        families: ['Roboto:300,400,500,700'],
        display: 'swap',
        version: 1.0,
      },
    };

    // Load Fonts
    WebFont.load(fontConfig);

    // Define String Literals
    type ScriptType = 'vue' | 'ts';

    // Define the selectors and src for dynamic imports
    const scripts: {
      selector: string;
      src: string;
      type: ScriptType;
    }[] = [
      {
        selector: '#tofino-alert', // Alert
        src: 'alert',
        type: 'ts',
      },
      {
        selector: '#main-menu', // Main menu
        src: 'menu',
        type: 'ts',
      },
      {
        selector: '.js-contact-form', // Search
        src: 'ContactForm', // VueJS Component name
        type: 'vue',
      },
    ];

    // Loop through the scripts and import the src
    scripts.forEach(({ selector, src, type }) => {
      const el: HTMLElement | null = document.querySelector(selector);

      if (el) {
        if (type === 'vue') {
          // Dynamically Import Vue Component
          createApp({
            components: {
              [src]: defineAsyncComponent(() => import(`../vue/${src}.vue`)),
            },
          })
            .provide(DefaultApolloClient, apolloClient)
            .use(createPinia())
            .mount(el);
        } else if (type === 'ts') {
          // Dynamically Import Typescript File
          import(`./modules/${src}.ts`).then(({ default: script }) => {
            script();
          });
        }
      } else {
        // console.warn(`Tofino Theme: Could not find ${selector} for script ${src}.ts.`);
      }
    });

    this.finalize();
  },
  finalize() {
    // JavaScript to be fired after init
  },
  loaded() {
    // Javascript to be fired once fully loaded
  },
};
