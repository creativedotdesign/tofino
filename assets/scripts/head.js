// Import loadCSS
import fgLoadcss from 'fg-loadcss';
global.loadCSS = fgLoadcss.loadCSS; // Make it global

/**
 * Tether (http://github.hubspot.com/tether/)
 *
 * Tether is a Bootstrap 4 dependency when including Tooltip or Popover
 * You must include Tether if including the full Bootstrap JS code or tooltip.js or popover.js
 */
import Tether from "tether";  // eslint-disable-line no-unused-vars
window.Tether = Tether;

// import WebFont from 'webfontloader'; // Web Font Loader (https://github.com/typekit/webfontloader)

// Google font example.
// WebFont.load({
//    google: {
//      families: ['Droid Sans', 'Droid Serif']
//    }
//  });
