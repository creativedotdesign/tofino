export default () => {
  const throttle = (func, time = 100) => {
    const lastTime = 0;
    return () => {
      const now = new Date();
      if (now - lastTime >= time) {
        func();
        time = now;
      }
    };
  };

  let lastScroll = 0;

  window.addEventListener(
    'scroll',
    throttle(() => {
      const currentScroll = window.scrollY;

      const body = document.body;

      if (currentScroll <= 0) {
        body.classList.remove('scroll-up');
        return;
      }

      if (currentScroll > lastScroll && !body.classList.contains('scroll-down')) {
        // down
        body.classList.remove('scroll-up');
        body.classList.add('scroll-down');
      } else if (currentScroll < lastScroll && body.classList.contains('scroll-down')) {
        // up
        body.classList.remove('scroll-down');
        body.classList.add('scroll-up');
      }
      lastScroll = currentScroll;
    }, 100)
  );
};
