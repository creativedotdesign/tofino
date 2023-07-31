// Import Font loader
import * as WebFont from 'webfontloader';
import { WebFontInterface } from '@/js/types';
import 'virtual:svg-icons-register';
import { loadScripts } from '@/js/scriptLoader';
import { Scripts } from '@/js/types';

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

    // Define the selectors and src for dynamic imports
    const scripts: Scripts = [
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

    // Load the scripts
    loadScripts(scripts);

    this.finalize();
  },
  finalize() {
    // JavaScript to be fired after init
  },
  loaded() {
    // Javascript to be fired once fully loaded
  },
};
