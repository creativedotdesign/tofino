export default () => {
  const getCookie = (cookieName: string) => {
    const value = `; ${document.cookie}`;
    const parts: string[] = value.split(`; ${cookieName}=`);

    if (parts.length === 2) {
      return parts.pop()?.split(';').shift();
    }
  };

  const alert: HTMLElement | null = document.getElementById('tofino-alert');

  if (alert) {
    const closeIcon: HTMLElement | null = document.querySelector('#tofino-alert .js-close');

    if (closeIcon) {
      closeIcon.addEventListener('click', () => {
        if (tofinoJS.cookieExpires) {
          const date: Date = new Date();

          date.setTime(date.getTime() + parseInt(tofinoJS.cookieExpires, 10) * 24 * 60 * 60 * 1000);

          const expires: string = 'expires=' + date.toUTCString();

          document.cookie = 'tofino-alert-closed=yes;' + expires + '; path=/';
        } else {
          document.cookie = 'tofino-alert-closed=yes;max=age=0; path=/';
        }

        alert.remove();
      });
    }

    if (tofinoJS.alertJS === 'true' && !getCookie('tofino-alert-closed')) {
      // Show the alert using JS based on the cookie (fixes html caching issue)
      alert.style.display = 'block';
    }
  }
};
