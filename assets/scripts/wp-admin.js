import Cookies from 'js-cookie';

document.addEventListener('DOMContentLoaded', () => {
  if (document.body.contains(document.querySelector('.maintenance-mode-alert'))) {
    const button = document.querySelector('.maintenance-mode-alert button');

    button.addEventListener('click', () => {
      Cookies.set('tofino_maintenance_alert_dismissed', 'true');

      document.querySelector('.maintenance-mode-alert').style.display = 'none';
    });
  }
});
