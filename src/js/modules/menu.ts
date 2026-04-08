import { lock, unlock } from 'tua-body-scroll-lock';

/**
 * Initialises the main navigation menu, wiring up toggle buttons,
 * body scroll locking, and keyboard (Escape key) dismissal.
 *
 * @returns void
 */
export const menu = (): void => {
  const menuEl = document.getElementById('main-menu');
  const hiddenClass = 'hidden';

  if (!menuEl) return;

  const openButton = document.querySelector<HTMLElement>(`[data-menu-open="${menuEl.id}"]`);
  const closeButtons = document.querySelectorAll<HTMLElement>(`[data-menu-close="${menuEl.id}"]`);

  const setExpandedState = (expanded: boolean): void => {
    openButton?.setAttribute('aria-expanded', expanded ? 'true' : 'false');
  };

  /**
   * Opens the main menu, removes the inactive class, adds the menu-open body class,
   * and locks body scroll.
   *
   * @returns void
   */
  const openMenu = (): void => {
    menuEl.classList.remove(hiddenClass);
    document.body.classList.add('menu-open');
    setExpandedState(true);
    lock(menuEl);
  };

  /**
   * Closes the main menu, restores the inactive class, removes the menu-open body class,
   * and unlocks body scroll.
   *
   * @returns void
   */
  const closeMenu = (): void => {
    menuEl.classList.add(hiddenClass);
    document.body.classList.remove('menu-open');
    setExpandedState(false);
    unlock(menuEl);
  };

  /**
   * Checks whether the menu is currently open.
   *
   * @returns true if the menu is open, false otherwise.
   */
  const isOpen = (): boolean => !menuEl.classList.contains(hiddenClass);

  setExpandedState(isOpen());

  openButton?.addEventListener('click', () => {
    if (!isOpen()) {
      openMenu();
    }
  });

  closeButtons.forEach((button) => {
    button.addEventListener('click', () => {
      if (isOpen()) {
        closeMenu();
      }
    });
  });

  document.addEventListener('keydown', (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isOpen()) {
      closeMenu();
    }
  });
};

export default menu;
