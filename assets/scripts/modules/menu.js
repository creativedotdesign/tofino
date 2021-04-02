// Scroll lock
import { disableBodyScroll, enableBodyScroll } from 'body-scroll-lock';

const Menu = () => {
  // Menu Toggle
  const buttons = document.querySelectorAll('.js-menu-toggle'),
    menu = document.getElementById('main-menu');

  buttons.forEach(el => {
    el.addEventListener('click', () => {
      // Toggle the hide class
      menu.classList.toggle('hidden');

      if (menu.classList.contains('hidden')) {
        disableBodyScroll(menu);
      } else {
        enableBodyScroll(menu);
      }
    });
  });

  // Close menu on ESC key
  document.onkeydown = e => {
    if (e.key === 'Escape' && !menu.classList.contains('hidden')) {
      menu.classList.add('hidden');

      enableBodyScroll(menu);
    }
  };
};

export default Menu;
