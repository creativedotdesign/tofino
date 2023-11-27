// Scroll lock
import { disableBodyScroll, enableBodyScroll, clearAllBodyScrollLocks } from 'body-scroll-lock';

export default () => {
  // Menu Toggle
  const buttons: NodeListOf<Element> | null = document.querySelectorAll('.js-menu-toggle');
  const menu: HTMLElement | null = document.getElementById('main-menu');

  if (menu) {
    buttons.forEach((el) => {
      el.addEventListener('click', () => {
        // Toggle the hide class
        menu.classList.toggle('inactive');

        if (menu.classList.contains('inactive')) {
          enableBodyScroll(menu);

          document.body.classList.remove('menu-open');
        } else {
          disableBodyScroll(menu);

          document.body.classList.add('menu-open');
        }
      });
    });

    // Close menu on ESC key
    document.onkeydown = (e) => {
      if (e.key === 'Escape' && !menu.classList.contains('inactive')) {
        menu.classList.add('inactive');

        document.body.classList.remove('menu-open');

        clearAllBodyScrollLocks();
      }
    };
  }
};
