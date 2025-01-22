import { Scripts } from '@/js/types/types';
import { loadScripts } from '@/js/helpers/scriptLoader';
// import * as WebFont from 'webfontloader';
// import { WebFontInterface } from '@/js/types/types';

// Import CSS
import '@/css/app.css';

const init = () => {
  // JavaScript to be fired on all pages

  // Config for WebFontLoader
  // const fontConfig: WebFontInterface = {
  //   classes: false,
  //   events: false,
  //   google: {
  //     families: ['Roboto:300,400,500,700'],
  //     display: 'swap',
  //     version: 1.0,
  //   },
  // };

  // // Load Fonts
  // WebFont.load(fontConfig);

  // Define the selectors and src for dynamic imports
  const scripts: Scripts = [
    {
      selector: '.alert', // Alert
      src: 'alerts',
      type: 'ts',
    },
    {
      selector: '#main-menu', // Main menu
      src: 'menu',
      type: 'ts',
    },
    {
      selector: '[data-iframe]', // iFrame
      src: 'iframe',
      type: 'ts',
    },
  ];

  // Load the scripts
  loadScripts(scripts);

  finalize();

  const issues = [];

  function warn(el) {
    // Style the detected issues
    // el.style.outline = '2px solid #FFCC00';
    // el.style.backgroundColor = '#FFCC00';
    el.style.backgroundImage = 'linear-gradient(135deg, rgba(255,0,0,1) 0%, rgba(255,204,0,1) 35%, rgba(0,212,255,1) 100%)';
  }

  function checkScrollingAncestor(elem) {
    if (!elem.parentElement || elem.parentElement.tagName.toLowerCase() === 'body') {
      return false;
    }

    const computedStyle = window.getComputedStyle(elem.parentElement);

    if (computedStyle.overflowX == 'auto') {
      return true;
    } else {
      return checkScrollingAncestor(elem.parentElement);
    }
  }

  function getSizedAncestor(elem) {
    if (!elem.parentElement) {
      return null;
    }

    if (elem.parentElement.scrollWidth > 0) {
      return elem.parentElement;
    } else {
      return getSizedAncestor(elem.parentElement);
    }
  }

  function checkElement(el) {



    const hasScrollingAncestor = checkScrollingAncestor(el);
    if (hasScrollingAncestor) {
      return;
    }

    const isHidden = (el.offsetParent === null);
    if (isHidden) {
      return;
    }



    // Find elements that overflow the document width
    if (el.scrollWidth > document.documentElement.offsetWidth) {

      console.log('Checking....', el);
      console.log('Document width:', document.documentElement.offsetWidth);
      console.log('Offset Element Width: ', el.scrollWidth);
      console.log(el.attributes);

      warn(el);
      issues.push(el);
    }

    const ancestor = getSizedAncestor(el);
    const info = window.getComputedStyle(el);

    // Find any negative margins (deliberate outflow)
    const adjustment =
      (info.marginLeft.startsWith('-') ? parseFloat(info.marginLeft) * -1 : 0)
      +
      (info.marginRight.startsWith('-') ? parseFloat(info.marginRight) * -1 : 0);

    if (ancestor && (el.scrollWidth - adjustment) > ancestor.scrollWidth) {
      warn(el);
      issues.push(el);
    }
  }

  // document.querySelectorAll('*').forEach(checkElement);

  // issues.length > 0 && issues[0].scrollIntoView();

  // console.log('Checking for issues....');

  // console.log(issues);

  // const getSelector = (el) => {
  //   if (!el) return '';
  //   if (el.tagName.toLowerCase() === 'html') return 'HTML';

  //   let selector = el.tagName;
  //   selector += el.id ? '#' + el.id : '';
  //   if (el.className) {
  //     const classes = el.className.split(/\s+/);
  //     selector += classes.map(cls => '.' + cls).join('');
  //   }
  //   return getSelector(el.parentElement) + ' > ' + selector;
  // };

  // document.querySelectorAll('*').forEach(el => {



  //   if (el) {

  //     console.log('Element: ', el);

  //     if (el.parentElement && (el.offsetWidth || el.offsetHeight)) {
  //       console.log('Haz offsetWidth', el.offsetWidth);
  //       console.log('Haz offsetHeight', el.offsetHeight);
  //       console.log('Checking...', (el.offsetWidth > el.parentElement.offsetWidth || el.offsetHeight > el.parentElement.offsetHeight))

  //       if (el.offsetWidth > el.parentElement.offsetWidth || el.offsetHeight > el.parentElement.offsetHeight) {
  //         warn(el);
  //         console.log('Overflowing Element:', getSelector(el));
  //       }


  //     }

  //     // console.log(el.offsetWidth);


  //   }


  // });


  // function isOverflowing(element) {
  //   return element.scrollWidth > element.clientWidth;
  // }

  // document.querySelectorAll('*').forEach(el => {
  //   if (isOverflowing(el)) {

  //     console.log(el);

  //     console.log(`Content is overflowing, scrollWidth is ${el.scrollWidth}px`);
  //   } else {
  //     `No overflows, scrollWidth is ${el.scrollWidth}px`;
  //   }
  // });

};

const finalize = () => {
  // JavaScript to be fired after init
};

const loaded = () => {
  // Javascript to be fired once fully loaded
};

// DOM Ready
window.addEventListener('DOMContentLoaded', () => {
  init();
});

// Fully loaded
window.addEventListener('load', () => {
  loaded();
});
