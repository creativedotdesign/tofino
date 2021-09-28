const Alert = () => {
  const getCookie = cookieName => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${cookieName}=`);
    if (parts.length === 2)
      return parts
        .pop()
        .split(';')
        .shift();
  };

  const alert = document.getElementById('tofino-notification');

  if (alert) {
    const closeIcon = document.querySelector('#tofino-notification .js-close');

    closeIcon.addEventListener('click', e => {
      if (tofinoJS.cookieExpires) {
        let date = new Date();

        date.setTime(date.getTime() + parseInt(tofinoJS.cookieExpires, 10) * 24 * 60 * 60 * 1000);

        const expires = 'expires=' + date.toUTCString();

        document.cookie = 'tofino-notification-closed=yes;' + expires + '; path=/';
      } else {
        document.cookie = 'tofino-notification-closed=yes;max=age=0; path=/';
      }

      alert.remove();
    });

    if (tofinoJS.notificationJS === 'true' && !getCookie('tofino-notification-closed')) {
      // Show the notfication using JS based on the cookie (fixes html caching issue)
      alert.style.display = 'block';
    }
  }
};

export default Alert;
