// Import Cookies
import Cookies from 'js-cookie';

const Alert = () => {
  const alert = document.getElementById('tofino-notification');

  if (alert) {
    const closeIcon = document.querySelector('#tofino-notification .js-close');

    closeIcon.addEventListener('click', e => {
      if (tofinoJS.cookieExpires) {
        Cookies.set('tofino-notification-closed', 'yes', {
          expires: parseInt(tofinoJS.cookieExpires, 10),
        });
      } else {
        Cookies.set('tofino-notification-closed', 'yes');
      }

      alert.remove();
    });

    if (tofinoJS.notificationJS === 'true' && !Cookies.get('tofino-notification-closed')) {
      // Show the notfication using JS based on the cookie (fixes html caching issue)
      alert.style.display = 'block';
    }
  }
};

export default Alert;
