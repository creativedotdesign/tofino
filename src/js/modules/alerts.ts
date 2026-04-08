/**
 * Reads a cookie value by name from the current document.
 *
 * @param name - The name of the cookie to retrieve.
 * @returns The cookie value, or undefined if the cookie is not set.
 */
const getCookie = (name: string): string | undefined => {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);

  if (parts.length === 2) {
    return parts.pop()?.split(';').shift();
  }
};

/**
 * Writes a cookie to the current document.
 *
 * @param name - The name of the cookie.
 * @param value - The value to store in the cookie.
 * @param days - Optional number of days until the cookie expires. Omitting this sets max-age=0.
 * @returns void
 */
const setCookie = (name: string, value: string, days?: number): void => {
  const expiry = days
    ? `expires=${new Date(Date.now() + days * 86400000).toUTCString()}`
    : 'max-age=0';

  document.cookie = `${name}=${value};${expiry};path=/`;
};

/**
 * Initialises dismissible alert banners on the page.
 * Reads per-alert cookies to suppress previously closed alerts, and sets
 * a cookie when the close button is clicked to persist the dismissed state.
 *
 * @returns void
 */
export const alerts = (): void => {
  const elements = document.querySelectorAll<HTMLElement>('.alert');

  if (!elements.length) return;

  const cookieExpires = tofinoJS.cookieExpires;

  elements.forEach((element) => {
    const alertId = element.dataset.alertId;

    if (!alertId) return;

    const cookieName = `tofino-alert-${alertId}-closed`;

    if (!getCookie(cookieName)) {
      element.style.display = 'block';
    }

    const closeButton = element.querySelector<HTMLElement>('.js-close');

    closeButton?.addEventListener('click', () => {
      const days = cookieExpires ? parseInt(cookieExpires[parseInt(alertId) - 1], 10) : 0;

      setCookie(cookieName, 'yes', days || undefined);
      element.remove();
    });
  });
};

export default alerts;
