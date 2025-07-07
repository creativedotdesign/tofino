export const detectOverflow = () => {
  const issues: string[] = [];

  // Track parent elements to skip if a child el is flagged
  const parentsToSkip = new Set<HTMLElement>();

  // Highlight issues in browser
  const warn = (el: HTMLElement) => {
    el.style.outline = '2px solid #FF0000';
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

  // Check each element
  const checkElement = (el: HTMLElement) => {
    // Skip if parent is already flagged
    if (parentsToSkip.has(el)) {
      return;
    }
    const hasScrollingAncestor = checkScrollingAncestor(el);
    if (hasScrollingAncestor) {
      return;
    }

    const isHidden = (el.offsetParent === null);
    if (isHidden) {
      return;
    }

    // Check if element is wider than document
    if (el.scrollWidth > document.documentElement.offsetWidth) {
      warn(el);
      issues.push(getSelector(el));

      // Track the parent to skip
      if (el.parentElement) {
        parentsToSkip.add(el.parentElement);
      }
    } else {
      const ancestor = getSizedAncestor(el);
      if (ancestor) {
        const info = window.getComputedStyle(el);
        // Negative margin check
        const adjustment =
          (info.marginLeft.startsWith('-') ? parseFloat(info.marginLeft) * -1 : 0) +
          (info.marginRight.startsWith('-') ? parseFloat(info.marginRight) * -1 : 0);

        if ((el.scrollWidth - adjustment) > ancestor.scrollWidth) {
          warn(el);
          issues.push(getSelector(el));
          // Skip if parent is already flagged
          if (el.parentElement) {
            parentsToSkip.add(el.parentElement);
          }
        }
      }
    }
  }

  // Loop over all elements in reverse, so children are processed first
  Array.from(document.querySelectorAll<HTMLElement>('*'))
    .reverse()
    .forEach(checkElement);

  return issues;
}
