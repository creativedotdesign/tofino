/**
 * Initialises the maintenance mode alert banner.
 * Wires up the dismiss button to hide the alert and set a cookie that
 * suppresses the banner for 24 hours on subsequent page loads.
 *
 * @returns void
 */
export const maintenanceMode = () => {
  if (document.querySelector('.maintenance-mode-alert')) {
    const button: HTMLElement | null = document.querySelector('.maintenance-mode-alert button');

    if (button) {
      button.addEventListener('click', () => {
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
};
