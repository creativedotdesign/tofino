// Scroll lock
import { disableBodyScroll, enableBodyScroll, clearAllBodyScrollLocks } from 'body-scroll-lock';

export default () => {
  const buttons = document.querySelectorAll<HTMLElement>('.js-menu-toggle');
  const menu = document.getElementById('main-menu');

  const toggleMenu = () => {
    if (menu) {
      menu.classList.toggle('inactive');

      if (menu.classList.contains('inactive')) {
        enableBodyScroll(menu);
        document.body.classList.remove('menu-open');
      } else {
        disableBodyScroll(menu);
        document.body.classList.add('menu-open');
      }
    }
  };

  const closeMenuOnEsc = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && menu && !menu.classList.contains('inactive')) {
      menu.classList.add('inactive');
      document.body.classList.remove('menu-open');
      clearAllBodyScrollLocks();
    }
  };

  if (menu) {
    buttons.forEach((el) => {
      el.addEventListener('click', toggleMenu);
    });

    document.addEventListener('keydown', closeMenuOnEsc);
  }
};
