import { Scripts } from '@/js/types/types';
import { loadScripts } from '@/js/helpers/scriptLoader';
import * as WebFont from 'webfontloader';
import { WebFontInterface } from '@/js/types/types';

// Import CSS
import '@/css/app.css';

const init = () => {
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

  // Define the selectors and src for dynamic imports
  const scripts: Scripts = [
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
  ];

  // Load the scripts
  loadScripts(scripts);

  finalize();
};

const finalize = () => {
  // JavaScript to be fired after init
};

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
