import iframeResize from '@iframe-resizer/parent';

export default () => {
  const options: IntersectionObserverInit = {
    root: null,
    rootMargin: '100px 0px',
    threshold: 1,
  };

  const iFrameModules = document.querySelectorAll<HTMLElement>('.module-iframe [data-iframe]');

  const handleIntersection = (
    entries: IntersectionObserverEntry[],
    observer: IntersectionObserver
  ) => {
    entries.forEach((entry) => {
      const isIntersecting = entry.isIntersecting || entry.intersectionRatio > 0;

      if (isIntersecting) {
        const iframe = entry.target.querySelector<HTMLIFrameElement>('iframe');

        if (iframe) {
          iframe.classList.add('active');
        }
        observer.unobserve(entry.target);
      }
    });
  };

  const handleIframeLoad = (iframe: HTMLIFrameElement, loading: HTMLElement | null) => {
    iframe.classList.add('loaded');

    if (loading) {
      loading.style.display = 'none';
    }
  };

  iFrameModules.forEach((iFrameModule) => {
    const iframe = iFrameModule.querySelector<HTMLIFrameElement>('iframe');
    const loading = iFrameModule.querySelector<HTMLElement>('.js-loading');

    if (iframe) {
      const observer = new IntersectionObserver(handleIntersection, options);

      observer.observe(iFrameModule);

      iframe.addEventListener('load', () => handleIframeLoad(iframe, loading));

      iframeResize(
        {
          checkOrigin: false,
          onScroll: ({ top }) => {
            window.scrollTo({
              top: top,
              behavior: 'smooth',
            });
            return true;
          },
          license: tofinoJS.iframeResizerLicense,
        },
        iframe
      );
    }
  });
};
