import iframeResize from '@iframe-resizer/parent';

export default () => {
  const options = {
    root: null,
    rootMargin: `100px 0px`,
    threshold: 1,
  };

  const iFrameModule: HTMLElement | null = document.querySelector('.module-iframe .js-main-div');
  const iframe: HTMLElement | null = document.querySelector('.module-iframe iframe');

  if (iFrameModule) {
    const observer = new IntersectionObserver((entries) => {
      const isIntersecting =
        typeof entries[0].isIntersecting === 'boolean'
          ? entries[0].isIntersecting
          : entries[0].intersectionRatio > 0;

      if (isIntersecting) {
        iframe?.classList.add('active');

        observer.unobserve(iFrameModule);
      }
    }, options);

    observer.observe(iFrameModule);
  }

  iframeResize(
    {
      license: 'GPLv3',
      waitForLoad: true,
    },
    iframe
  );
};
