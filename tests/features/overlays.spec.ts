import { test, expect, type Page } from '@playwright/test';
import { overlays, tabbableSelector } from '../support/config';
import { dismissOverlays } from '../support/utils/page';

/**
 * Overlay / dialog behaviour. For each overlay in tests/support/config.ts that
 * exists on the page (header menus, search) plus any native <dialog> opener:
 *
 *  1. opening moves focus inside the container;
 *  2. the container is a dialog (native <dialog> or role="dialog" + aria-modal);
 *  3. Tab is contained: cycling past the last tabbable stays inside;
 *  4. Escape closes it and focus returns to the trigger.
 *
 * Runs at a phone width (menus) and desktop (search, dialogs). Overlays whose
 * trigger is not visible at a width are skipped there.
 */
/** Viewports to test at: menus are usually phone-only, search and dialogs desktop. */
const widths = [
  { name: 'phone', width: 500, height: 900 },
  { name: 'desktop', width: 1280, height: 800 },
];

/** True when document.activeElement is inside the element matching `container`. */
const activeIsInside = (page: Page, container: string): Promise<boolean> =>
  page.evaluate((sel) => {
    const el = document.querySelector(sel);
    return !!el && el.contains(document.activeElement);
  }, container);

test.describe('Overlays behave as modal dialogs', () => {
  for (const size of widths) {
    test(`at ${size.name} width`, async ({ page }) => {
      await page.setViewportSize({ width: size.width, height: size.height });
      await page.goto('/');
      await page.waitForTimeout(500);
      await dismissOverlays(page);

      // Native <dialog> openers are self-describing; add them to the list.
      const dialogOpeners = await page.$$eval(
        'button[aria-haspopup="dialog"][aria-controls]',
        (buttons) =>
          buttons.map((b) => ({
            name: `dialog #${b.getAttribute('aria-controls')}`,
            trigger: `button[aria-controls="${b.getAttribute('aria-controls')}"]`,
            container: `#${b.getAttribute('aria-controls')}`,
          })),
      );
      const candidates = [
        ...overlays,
        ...dialogOpeners.filter((d) => !overlays.some((o) => o.container === d.container)),
      ];

      let checked = 0;

      for (const overlay of candidates) {
        const trigger = page.locator(overlay.trigger).first();
        if (!(await trigger.count()) || !(await trigger.isVisible())) continue;

        await test.step(overlay.name, async () => {
          checked += 1;
          const container = page.locator(overlay.container).first();

          // Open by keyboard: Safari does not focus a button on click, so a
          // click would make "focus returns to the trigger" untestable there.
          await trigger.focus();
          await trigger.press('Enter');
          await expect(container, `${overlay.name}: container visible after opening`).toBeVisible();

          // 1. Focus moved in
          expect
            .soft(
              await activeIsInside(page, overlay.container),
              `${overlay.name}: focus moves into the overlay on open`,
            )
            .toBe(true);

          // 2. Dialog semantics
          const semantics = await container.evaluate((el) => ({
            tag: el.tagName.toLowerCase(),
            role: el.getAttribute('role'),
            modal: el.getAttribute('aria-modal'),
            labelled: el.hasAttribute('aria-label') || el.hasAttribute('aria-labelledby'),
          }));
          const isDialog =
            semantics.tag === 'dialog' ||
            (semantics.role === 'dialog' && semantics.modal === 'true');
          expect
            .soft(
              isDialog,
              `${overlay.name}: container is a <dialog> or role="dialog" aria-modal="true" (got <${semantics.tag} role=${semantics.role}>)`,
            )
            .toBe(true);
          expect
            .soft(semantics.labelled, `${overlay.name}: dialog has aria-label or aria-labelledby`)
            .toBe(true);

          // 3. Focus containment
          const tabbables = await container.locator(tabbableSelector).count();
          for (let i = 0; i < tabbables + 2; i += 1) {
            await page.keyboard.press('Tab');
          }
          expect
            .soft(
              await activeIsInside(page, overlay.container),
              `${overlay.name}: focus stays inside after tabbing past the last control`,
            )
            .toBe(true);

          // 4. Escape closes and returns focus
          await page.keyboard.press('Escape');
          await expect.soft(container, `${overlay.name}: Escape closes the overlay`).toBeHidden();
          const returned = await page.evaluate(
            (sel) => document.activeElement === document.querySelector(sel),
            overlay.trigger,
          );
          expect
            .soft(returned, `${overlay.name}: focus returns to the trigger on close`)
            .toBe(true);

          // Leave the page clean for the next overlay.
          if (await container.isVisible()) {
            await page.keyboard.press('Escape');
            if (overlay.close)
              await page
                .locator(overlay.close)
                .first()
                .click({ force: true })
                .catch(() => {});
          }
        });
      }

      test.info().annotations.push({
        type: 'overlays',
        description: `${checked} overlay(s) checked at ${size.name}`,
      });

      if (size.name === 'phone') {
        // Every site has a mobile menu. Finding none means a trigger selector
        // is stale, so fail rather than silently lose the coverage.
        expect(
          checked,
          'No menu or dialog trigger found at phone width. Declare the menu under "a11y.overlays" in its feature manifest (docs/testing.md).',
        ).toBeGreaterThan(0);
      } else if (!checked) {
        test.skip(true, `No overlays present at ${size.name} width`);
      }
    });
  }
});
