/**
 * Initialises a scroll-direction detector that adds `scroll-up` or `scroll-down`
 * classes to `document.body`, allowing CSS to show or hide a sticky header based
 * on scroll direction. Uses requestAnimationFrame for performance.
 *
 * @returns void
 */
export const menuScrollReveal = (): void => {
  let lastScroll = 0;
  let ticking = false;

  /**
   * Scroll event handler that compares the current scroll position to the last
   * recorded position and updates `scroll-up` / `scroll-down` body classes accordingly.
   * Batches work inside a requestAnimationFrame callback and uses a ticking flag
   * to prevent redundant frames.
   *
   * @returns void
   */
  const onScroll = (): void => {
    if (ticking) return;

    ticking = true;
    requestAnimationFrame(() => {
      const currentScroll = window.scrollY;
      const { classList } = document.body;

      if (currentScroll <= 0) {
        classList.remove('scroll-up');
      } else if (currentScroll > lastScroll) {
        classList.remove('scroll-up');
        classList.add('scroll-down');
      } else if (currentScroll < lastScroll) {
        classList.remove('scroll-down');
        classList.add('scroll-up');
      }

      lastScroll = currentScroll;
      ticking = false;
    });
  };

  window.addEventListener('scroll', onScroll, { passive: true });
};

export default menuScrollReveal;
