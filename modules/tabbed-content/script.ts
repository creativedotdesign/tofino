/**
 * Tabbed Content module — client behaviour.
 *
 * Handles tab switching with keyboard arrow-key navigation and
 * ARIA state management (aria-selected, hidden attribute).
 *
 * @returns void
 */
const init = (): void => {
  const modules = document.querySelectorAll<HTMLElement>('[data-module="tabbed-content"]');

  modules.forEach((module) => {
    const tabs = module.querySelectorAll<HTMLButtonElement>('[role="tab"]');
    const panels = module.querySelectorAll<HTMLElement>('[role="tabpanel"]');

    const activate = (index: number): void => {
      tabs.forEach((tab, i) => {
        const active = i === index;
        tab.setAttribute('aria-selected', active ? 'true' : 'false');
        tab.classList.toggle('border-gray-900', active);
        tab.classList.toggle('text-gray-900', active);
        tab.classList.toggle('border-transparent', !active);
      });

      panels.forEach((panel, i) => {
        panel.toggleAttribute('hidden', i !== index);
      });
    };

    tabs.forEach((tab, i) => {
      tab.addEventListener('click', () => activate(i));

      tab.addEventListener('keydown', (e: KeyboardEvent) => {
        if (e.key === 'ArrowRight') {
          const next = (i + 1) % tabs.length;
          activate(next);
          tabs[next].focus();
        } else if (e.key === 'ArrowLeft') {
          const prev = (i - 1 + tabs.length) % tabs.length;
          activate(prev);
          tabs[prev].focus();
        }
      });
    });
  });
};

export default init;
