// Import loadCSS
import fgLoadcss from 'fg-loadcss';
global.loadCSS = fgLoadcss.loadCSS; // Make it global

import WebFont from 'webfontloader';


// Google font example.
WebFont.load({
   google: {
     families: ['Droid Sans', 'Droid Serif']
   }
 });
