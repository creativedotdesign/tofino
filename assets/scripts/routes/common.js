// We need jQuery
var $ = window.jQuery;

// Import svg4everybody
import svg4everybody from 'svg4everybody';

// Import Cookies
import Cookies from 'js-cookie';

// Import stickyfill
var Stickyfill = require('stickyfill-web-module')();

// Headroom
import Headroom from 'headroom.js/dist/headroom.js';
window.Headroom = Headroom;

// Headroom jQuery
import 'headroom.js/dist/jQuery.headroom.js';

export default {
  init() {
    // JavaScript to be fired on all pages

    // Headroom JS
    $("nav.headroom").headroom();

    //Iniitalize svg4everybody
    svg4everybody();

    // List for notication close
    $('#tofino-notification .close').on('click', function() {
      if (tofinoJS.cookieExpires) {
        Cookies.set('tofino-notification-closed', 'yes', {expires: parseInt(tofinoJS.cookieExpires)});
      } else {
        Cookies.set('tofino-notification-closed', 'yes');
      }
    });

    // Show the notfication using JS based on the cookie (fixes html caching issue)
    if (tofinoJS.notificationJS === 'true' && !Cookies.get('tofino-notification-closed')) {
      $('#tofino-notification').show();
    }

    // Assign sticky
    var $sticky = document.getElementsByClassName('sticky-top');
    if ($sticky.length) {
      Stickyfill.add($sticky[0]);
    }
  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
  },
  loaded() {
    // Javascript to be fired on page once fully loaded
  }
};
