import { createApp, defineAsyncComponent } from 'vue';
import { DefaultApolloClient } from '@vue/apollo-composable';
import { ApolloClient, createHttpLink, InMemoryCache } from '@apollo/client/core';
import { createPinia } from 'pinia';
import { Scripts } from './types';

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

export const loadScripts = async (scripts: Scripts) => {
  // Loop through the scripts and import the src
  scripts.forEach(({ selector, src, type }) => {
    const el: HTMLElement | null = document.querySelector(selector);

    if (el) {
      if (type === 'vue') {
        createApp({
          components: {
            [src]: defineAsyncComponent(() => import(`../vue/${src}.vue`)),
          },
        })
          .provide(DefaultApolloClient, apolloClient)
          .use(createPinia())
          .mount(el);

        // console.log(`Tofino Theme: Loaded ${selector} for Vue component ${src}.vue.`);
      } else if (type === 'ts') {
        // Dynamically Import Typescript File
        import(`./modules/${src}.ts`).then(({ default: script }) => {
          script();
        });

        // console.log(`Tofino Theme: Loaded ${selector} for script ${src}.ts.`);
      }
    }
  });
};
