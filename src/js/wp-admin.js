document.addEventListener('DOMContentLoaded', () => {
  if (document.querySelector('.maintenance-mode-alert')) {
    const button = document.querySelector('.maintenance-mode-alert button');

    button.addEventListener('click', () => {
      let date = new Date();

      date.setTime(date.getTime() + 1 * 24 * 60 * 60 * 1000);

      const expires = 'expires=' + date.toUTCString();

      document.cookie = 'tofino_maintenance_alert_dismissed=true;' + expires + '; path=/';

      document.querySelector('.maintenance-mode-alert').style.display = 'none !important;';
    });
  }
});
