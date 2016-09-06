// Assign jQuery to $
var $ = window.jQuery;

// Import router
import Router from './router';

// Import local deps
import common from './routes/common';

// Import ajaxForm
import './ajax-form';

/**
 * Bootstrap Components
 *
 * If your build requires Tooltip or Popover then you must include Tether. See
 * head.js for Tether include code.
 */
 // import "bootstrap/js/dist/util.js";
 // import "bootstrap/js/dist/alert.js";
 // import "bootstrap/js/dist/button.js";
 // import "bootstrap/js/dist/carousel.js";
 import "bootstrap/js/dist/collapse.js"; // Mobile menu
 import "bootstrap/js/dist/dropdown.js"; // Menu dropdown
 // import "bootstrap/js/dist/modal.js";
 // import "bootstrap/js/dist/scrollspy.js";
 // import "bootstrap/js/dist/tab.js";
 // import "bootstrap/js/dist/tooltip.js"; // Requires tether
 // import "bootstrap/js/dist/popover.js"; // Requires tether

// Use this variable to set up the common and page specific functions. If you
// rename this variable, you will also need to rename the namespace below.
const routes = {
  // All pages
  common
};

// Load Events
$(document).ready(() => new Router(routes).loadEvents());
