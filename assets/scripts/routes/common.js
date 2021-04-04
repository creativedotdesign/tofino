// Import Alert
import Alert from '../modules/alert';

// Menu
import Menu from '../modules/menu';

export default {
  init () {
    // JavaScript to be fired on all pages

    // Alert
    if (document.body.contains(document.getElementById('tofino-notification'))) {
      Alert();
    }

    if (document.body.contains(document.getElementById('main-menu'))) {
      Menu();
    }
  },
  finalize () {
    // JavaScript to be fired on all pages, after page specific JS is fired
  },
  loaded () {
    // Javascript to be fired on page once fully loaded
  },
};
