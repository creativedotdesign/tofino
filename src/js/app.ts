import type { Script } from '@/js/types/types';
import { loadScripts } from '@/js/helpers/scriptLoader';
// import * as WebFont from 'webfontloader';
// import { WebFontInterface } from '@/js/types/types';

// Import CSS
import '@/css/app.css';

/**
 * Runs on DOMContentLoaded. Registers dynamic script configurations and
 * delegates loading to {@link loadScripts}. Calls {@link finalize} when complete.
 *
 * @returns void
 */
const init = () => {
  // JavaScript to be fired on all pages

  // Config for WebFontLoader
  // const fontConfig: WebFontInterface = {
  //   classes: false,
  //   events: false,
  //   google: {
  //     families: ['Roboto:300,400,500,700'],
  //     display: 'swap',
  //     version: 1.0,
  //   },
  // };

  // // Load Fonts
  // WebFont.load(fontConfig);

  // Define the selectors and src for dynamic imports
  const scripts: Script[] = [
    {
      selector: '.alert', // Alert
      src: 'alerts',
      type: 'ts',
    },
    {
      selector: '#main-menu', // Main menu
      src: 'menu',
      type: 'ts',
    },
    {
      selector: '[data-iframe]', // iFrame
      src: 'iframe',
      type: 'ts',
    },
    {
      selector: '[data-scroll-reveal]', // Menu scroll reveal
      src: 'menuScrollReveal',
      type: 'ts',
    },
    {
      selector: '[data-test-vue]', // Vue test
      src: 'TestComponent',
      type: 'vue',
    },
  ];

  // Load the scripts
  loadScripts(scripts);

  finalize();
};

/**
 * Runs after {@link init} completes. Intended for any JavaScript that must
 * execute after the initial scripts have been loaded.
 *
 * @returns void
 */
const finalize = () => {
  // JavaScript to be fired after init
};

/**
 * Runs on the window `load` event, after all resources (images, stylesheets,
 * sub-frames) have finished loading.
 *
 * @returns void
 */
const loaded = () => {
  // Javascript to be fired once fully loaded
};

// DOM Ready
window.addEventListener('DOMContentLoaded', () => {
  init();
});

// Fully loaded
window.addEventListener('load', () => {
  loaded();
});
