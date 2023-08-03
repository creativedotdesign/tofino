export default () => {
  const getCookie = (cookieName: string) => {
    const value = `; ${document.cookie}`;
    const parts: string[] = value.split(`; ${cookieName}=`);

    if (parts.length === 2) {
      return parts.pop()?.split(';').shift();
    }
  };

  const alerts: NodeListOf<HTMLElement> = document.querySelectorAll('.alert');

  if (alerts) {
    const expires = tofinoJS.cookieExpires;

    alerts.forEach((element) => {
      const alertId = element.dataset.alertId;

      if (!getCookie('tofino-alert-' + alertId + '-closed')) {
        // Show the alert using JS based on the cookie (fixes html caching issue)
        element.style.display = 'block';
      }

      const closeIcon: HTMLElement | null = element.querySelector('.js-close');

      if (closeIcon) {
        closeIcon.addEventListener('click', () => {
          const expiresValue: string = expires[alertId];

          if (expiresValue) {
            const date: Date = new Date();
            date.setTime(
              date.getTime() + parseInt(tofinoJS.cookieExpires, 10) * 24 * 60 * 60 * 1000
            );
            const expires: string = 'expires=' + date.toUTCString();
            document.cookie = 'tofino-alert-' + alertId + '-closed=yes;' + expires + '; path=/';
          } else {
            document.cookie = 'tofino-alert-' + alertId + '-closed=yes;max=age=0; path=/';
          }

          element.remove();
        });
      }
    });
  }
};
