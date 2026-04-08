import iframeResize from '@iframe-resizer/parent';

/**
 * Initialises iframe modules on the page.
 * Uses an IntersectionObserver to lazily activate iframes as they scroll into view,
 * hides the loading indicator once the iframe has loaded, and applies iframe-resizer
 * so the iframe height tracks its content.
 *
 * @returns void
 */
export const iframe = (): void => {
  const modules = document.querySelectorAll<HTMLElement>('.module-iframe [data-iframe]');

  if (!modules.length) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        const iframe = entry.target.querySelector<HTMLIFrameElement>('iframe');
        iframe?.classList.add('active');
        observer.unobserve(entry.target);
      });
    },
    { rootMargin: '100px 0px', threshold: 0 }
  );

  modules.forEach((module) => {
    const iframeEl = module.querySelector<HTMLIFrameElement>('iframe');

    if (!iframeEl) return;

    observer.observe(module);

    const loading = module.querySelector<HTMLElement>('.js-loading');

    iframeEl.addEventListener('load', () => {
      iframeEl.classList.add('loaded');
      if (loading) loading.style.display = 'none';
    });

    iframeResize(
      {
        checkOrigin: false,
        onScroll: ({ y }) => {
          window.scrollTo({ top: y, behavior: 'smooth' });
          return false;
        },
        license: tofinoJS.iframeResizerLicense ?? '',
      },
      iframeEl
    );
  });
};

export default iframe;
