// Import CSS
import '../css/base/admin.css';

document.addEventListener('DOMContentLoaded', () => {

  console.log('Tofino Theme: Admin');

  if (document.querySelector('.maintenance-mode-alert')) {
    const button: HTMLElement | null = document.querySelector('.maintenance-mode-alert button');

    if (button) {
      button.addEventListener('click', () => {
        console.log('Tofino Theme: Maintenance mode alert button clicked');

        const date = new Date();

        date.setTime(date.getTime() + 1 * 24 * 60 * 60 * 1000);

        const expires = 'expires=' + date.toUTCString();

        document.cookie = 'tofino_maintenance_alert_dismissed=true;' + expires + '; path=/';

        const alert: HTMLElement | null = document.querySelector('.maintenance-mode-alert');

        if (alert) {
          // Hide the alert
          alert.style.display = 'none';
        }
      });
    }
  }
});
