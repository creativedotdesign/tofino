// Import Alert
import Alert from './modules/alert';

// Menu
import Menu from './modules/menu';

// Import Font loader
import WebFont from 'webfontloader';

export default {
  init () {
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
      Alert();
    }

    // Menu
    if (document.getElementById('main-menu')) {
      Menu();
    }
  },
  finalize () {
    // JavaScript to be fired after init
  },
  loaded () {
    // Javascript to be fired once fully loaded
  },
};
