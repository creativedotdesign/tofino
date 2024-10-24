import iframeResize from '@iframe-resizer/parent';

export default () => {
  const options = {
    root: null,
    rootMargin: `100px 0px`,
    threshold: 1,
  };

  const iFrameModules: NodeListOf<HTMLElement> = document.querySelectorAll(
    '.module-iframe [data-iframe]'
  );

  iFrameModules.forEach((iFrameModule) => {
    const iframe: HTMLIFrameElement | null = iFrameModule.querySelector('iframe');
    const loading: HTMLElement | null = iFrameModule.querySelector('.js-loading');

    if (iframe) {
      const observer = new IntersectionObserver((entries) => {
        const isIntersecting =
          typeof entries[0].isIntersecting === 'boolean'
            ? entries[0].isIntersecting
            : entries[0].intersectionRatio > 0;

        if (isIntersecting) {
          iframe.classList.add('active');

          observer.unobserve(iFrameModule);
        }
      }, options);

      observer.observe(iFrameModule);

      iframe.addEventListener('load', () => {
        iframe.classList.add('loaded');

        if (loading) {
          loading.style.display = 'none';
        }
      });

      iframeResize(
        {
          onScroll: ({ top }) => {
            window.scrollTo({
              top: top,
              behavior: 'smooth',
            });

            return false; // Stop iframe-resizer scrolling the page
          },
          license: 'GPLv3',
          waitForLoad: true,
        },
        iframe
      );
    }
  });
};
