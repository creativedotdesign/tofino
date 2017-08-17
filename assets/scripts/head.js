// Import loadCSS
import fgLoadcss from 'fg-loadcss';
global.loadCSS = fgLoadcss.loadCSS; // Make it global

import Popper from 'popper.js/dist/umd/popper';
window.Popper = Popper;

// import WebFont from 'webfontloader'; // Web Font Loader (https://github.com/typekit/webfontloader)

// Google font example.
// WebFont.load({
//    google: {
//      families: ['Droid Sans', 'Droid Serif']
//    }
//  });
