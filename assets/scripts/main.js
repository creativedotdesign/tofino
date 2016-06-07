// Assign jQuery to $
var $ = window.jQuery;

// Import router
import Router from './router.js';

// Import svg4everybody
import svg4everybody from 'svg4everybody';

// Import stickyfill
import 'Stickyfill/src/stickyfill.js';

// Import ajaxForm
import './ajax-form.js';

/**
 * Tether (http://github.hubspot.com/tether/)
 *
 * Tether is a Bootstrap 4 dependency when including Tooltip or Popover
 * You must include Tether if including the full Bootstrap JS code or tooltip.js or popover.js
 */

// var Tether = require('tether');
// global.Tether = Tether; // Make it global

/**
 * Bootstrap Components
 *
 * If your build requires Tooltip or Popover then you must require('bootstrap')
 * in order for Tether to work. This should be fixed in a future Bootstrap release.
 */

// Include the full Bootstrap JS lib.
// require('bootstrap'); // Requires tether
// import 'bootstrap'; // Requires tether

// Selectively include the Boostrap comonents you need for your build.
// import "bootstrap/dist/js/umd/util.js";
// import "bootstrap/dist/js/umd/alert.js";
// import "bootstrap/dist/js/umd/button.js";
// import "bootstrap/dist/js/umd/carousel.js";
import "bootstrap/dist/js/umd/collapse.js"; // Mobile menu
import "bootstrap/dist/js/umd/dropdown.js"; // Menu dropdown
// import "bootstrap/dist/js/umd/modal.js";
// import "bootstrap/dist/js/umd/scrollspy.js";
// import "bootstrap/dist/js/umd/tab.js";
// import "bootstrap/dist/js/umd/tooltip.js"; // Requires tether
// import "bootstrap/dist/js/umd/popover.js"; // Requires tether

// Use this variable to set up the common and page specific functions. If you
// rename this variable, you will also need to rename the namespace below.
var tofino = {
  // JavaScript to be fired on all pages
  common: {
    init: function() {

      //Iniitalize svg4everybody
      svg4everybody();

      $('.form-processor').ajaxForm({
        beforeSerializeData: function() {
          console.log('Run this before data serialize!');
        },
        afterSucess: function() {
          console.log('Sucess!');
        }
      });

      //List for notication close
      $('#tofino-notification .close').on('click', function() {
        if (tofinoJS.cookieExpires) {
          Cookies.set('tofino-notification-closed', 'yes', {expires: parseInt(tofinoJS.cookieExpires)});
        } else {
          Cookies.set('tofino-notification-closed', 'yes');
        }
      });

      //Assign sticky
      var $sticky = $('.navbar-sticky-top');

      if ($sticky.length) {
        //Sticky polyfill for css position: sticky
        $sticky.Stickyfill();

        //Assign stick offset
        var stickyTop = $sticky.offset().top;

        $(window).scroll(function() {
          if ($(this).scrollTop() > stickyTop) {
            $sticky.addClass('stuck');
          } else {
            $sticky.removeClass('stuck');
          }
        });
      }
    }
  },
  // Home page
  home: {
    init: function() {
      // JavaScript to be fired on the home page
    },
    finalize: function() {
      // JavaScript to be fired on the home page, after the init JS
    }
  }
};

// Load Events
$(document).ready(function() {
  new Router(tofino).loadEvents();
});
