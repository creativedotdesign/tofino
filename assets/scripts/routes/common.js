// Import Alert
import Alert from '../modules/alert';

export default {
  init() {
    // JavaScript to be fired on all pages

    console.log('Testing DOM READY!');

    // Alert
    if (document.body.contains(document.getElementById('tofino-notification'))) {
      Alert();
    }
  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
  },
  loaded() {
    // Javascript to be fired on page once fully loaded
  },
};
