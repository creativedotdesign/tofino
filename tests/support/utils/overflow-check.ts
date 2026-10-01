/**
 * Detects overflowing elements in the current document and returns selectors
 * for nodes that exceed viewport/container width.
 *
 * @returns CSS-like selector paths for detected overflow issues.
 */
export const detectOverflow = (exclude: string[] = []) => {
  // Third-party containers (manifest/site `exclude`): skip their contents.
  const excludeRoots = exclude.flatMap((selector) => {
    try {
      return Array.from(document.querySelectorAll<HTMLElement>(selector));
    } catch {
      return [];
    }
  });
  const issues: string[] = [];

  // Track parent elements to skip if a child el is flagged
  const parentsToSkip = new Set<HTMLElement>();

  /**
   * Highlights an element with a red outline for visual debugging.
   */
  const warn = (el: HTMLElement) => {
    el.style.outline = '2px solid #FF0000';
  };

  /**
   * Recursively checks whether an element has an overflow-x:auto ancestor.
   */
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
  };

  /**
   * Returns nearest ancestor with non-zero width.
   */
  const getSizedAncestor = (elem: HTMLElement): HTMLElement | null => {
    if (!elem.parentElement) {
      return null;
    }

    if (elem.parentElement.offsetWidth > 0) {
      return elem.parentElement;
    } else {
      return getSizedAncestor(elem.parentElement);
    }
  };

  /**
   * Builds a readable selector path for reporting.
   */
  const getSelector = (el: HTMLElement | null): string => {
    if (el == null) {
      return '';
    }

    if (el.tagName.toLowerCase() == 'html') {
      return 'HTML';
    }

    let selector = el.tagName;
    selector += el.id ? '#' + el.id : '';

    if (el.className) {
      const classes = el.className.split(/\s/);
      for (let i = 0; i < classes.length; i++) {
        selector += '.' + classes[i];
      }
    }
    return getSelector(el.parentElement) + ' > ' + selector;
  };

  /**
   * Evaluates a single element for potential horizontal overflow.
   */
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

    if (excludeRoots.some((root) => root !== el && root.contains(el))) {
      return;
    }

    const isHidden = el.offsetParent === null;
    if (isHidden) {
      return;
    }

    // Screen-reader-only text (sr-only / screen-reader-text) is parked
    // off-canvas by design: 1px, clipped, absolutely positioned. It never
    // causes a horizontal scrollbar. The same goes for visibility:hidden
    // measuring nodes and pseudo-element hosts with no box.
    const style = window.getComputedStyle(el);
    const clipped =
      (style.clip && style.clip !== 'auto' && style.clip !== 'none') ||
      (style.clipPath && style.clipPath !== 'none');
    if (
      style.visibility === 'hidden' ||
      (clipped && style.position === 'absolute') ||
      (el.offsetWidth <= 1 && el.offsetHeight <= 1) ||
      /\b(sr-only|screen-reader-text|visually-hidden)\b/.test(el.className)
    ) {
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

        // Inline ancestors report scrollWidth 0; fall back to the box width.
        const ancestorWidth = Math.max(
          ancestor.scrollWidth,
          Math.ceil(ancestor.getBoundingClientRect().width),
        );

        if (el.scrollWidth - adjustment > ancestorWidth) {
          warn(el);
          issues.push(getSelector(el));
          // Skip if parent is already flagged
          if (el.parentElement) {
            parentsToSkip.add(el.parentElement);
          }
        }
      }
    }
  };

  // Loop over all elements in reverse, so children are processed first
  Array.from(document.querySelectorAll<HTMLElement>('*')).reverse().forEach(checkElement);

  return issues;
};
