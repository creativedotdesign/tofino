// export const detectOverflowUtils = () => {
const issues = [];

const warn = (el: HTMLElement) => {
  // Style the detected issues
  el.style.outline = '2px solid #FFCC00';
  el.style.backgroundColor = '#FFCC00';
  el.style.backgroundImage = 'linear-gradient(135deg, rgba(255,0,0,1) 0%, rgba(255,204,0,1) 35%, rgba(0,212,255,1) 100%)';
}

const checkScrollingAncestor = (elem: HTMLElement): boolean => {
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

const getSizedAncestor = (elem: HTMLElement): HTMLElement | null => {
  if (!elem.parentElement) {
    return null;
  }

  if (elem.parentElement.offsetWidth > 0) {
    return elem.parentElement;
  } else {
    return getSizedAncestor(elem.parentElement);
  }
}

const getSelector = (el: HTMLElement | null): string => {
  if (el == null) {
    return '';
  }

  if (el.tagName.toLowerCase() == 'html') {
    return 'HTML';
  }

  let selector = el.tagName;
  selector += (el.id) ? '#' + el.id : '';

  if (el.className) {
    const classes = el.className.split(/\s/);
    for (let i = 0; i < classes.length; i++) {
      selector += '.' + classes[i]
    }
  }
  return getSelector(el.parentElement) + ' > ' + selector;
}

export const checkElement = (el) => {
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

    console.log('Got issues', getSelector(el));

    // return getSelector(el);

    // issues.push(el);
    // issues.push(getSelector(el));
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
    // issues.push(el);

    console.log('Got issues', getSelector(el));

    // return getSelector(el);

    // issues.push(getSelector(el));
  }
}
// }

export function testFunction(elm) {
  console.log('Hi there!', elm);

  checkElement(elm);
}