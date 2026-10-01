import type { Page } from '@playwright/test';
import { dismiss } from '../config';

/** Click any cookie banner / popup dismiss control present, then wait for it to go. */
export const dismissOverlays = async (page: Page): Promise<void> => {
  for (const selector of dismiss) {
    const control = page.locator(selector).first();
    if ((await control.count()) && (await control.isVisible().catch(() => false))) {
      await control.click({ timeout: 2000 }).catch(() => {});
      await page.waitForTimeout(300);
    }
  }
};

/**
 * Brings a loaded page to the state a reader who has scrolled through it
 * would see: waits for the load event, scrolls through the document in
 * viewport steps so IntersectionObserver work fires (lazy images, Swiper lazy
 * slides, reveal-on-scroll), returns to the top, then waits for in-flight
 * images and fonts.
 *
 * Once-only reveal-on-scroll content stays revealed afterwards; with
 * "Replay Reveals" on it is re-hidden when scrolled out of view.
 */
export const settlePage = async (page: Page): Promise<void> => {
  await page.waitForLoadState('load').catch(() => {});

  await page.evaluate(async () => {
    const pause = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms));
    const step = Math.max(200, Math.floor(window.innerHeight * 0.8));
    const height = () => document.documentElement.scrollHeight;

    for (let y = 0; y < height(); y += step) {
      window.scrollTo(0, y);
      await pause(90);
    }
    window.scrollTo(0, height());
    await pause(150);
    window.scrollTo(0, 0);
  });

  // Images that became eligible while scrolling, plus web fonts; capped so a
  // broken image or slow CDN cannot stall the audit.
  await page
    .evaluate(() =>
      Promise.race([
        Promise.all([
          document.fonts?.ready,
          ...Array.from(document.images)
            .filter((img) => !img.complete)
            .map(
              (img) =>
                new Promise<void>((resolve) => {
                  img.addEventListener('load', () => resolve(), { once: true });
                  img.addEventListener('error', () => resolve(), { once: true });
                }),
            ),
        ]),
        new Promise((resolve) => setTimeout(resolve, 4000)),
      ]),
    )
    .catch(() => {});

  await page.waitForTimeout(300);
};

/**
 * Full-page screenshot of the current page for a report (opt-in via
 * A11Y_SCREENSHOTS=1). Dismisses banners so they don't cover the content,
 * settles the page so lazy images and once-only reveals have rendered,
 * outlines the elements matching `mark` with the `ada-violation` class, then
 * captures. Sites with "Replay Reveals" on re-hide off-screen content, so
 * their pictures will show blanks below the fold; audit with it set to Once.
 *
 * @param page  The page, already at the URL to capture.
 * @param filePath Where to write the PNG.
 * @param mark  Selectors of elements to outline in the capture.
 */
export const capturePage = async (
  page: Page,
  filePath: string,
  mark: string[] = [],
): Promise<void> => {
  await dismissOverlays(page);
  await settlePage(page);

  await page.evaluate((selectors) => {
    selectors.forEach((selector) => {
      try {
        document.querySelector(selector)?.classList.add('ada-violation');
      } catch {
        /* a computed selector may not round-trip; the report still lists it */
      }
    });
  }, mark);

  await page.screenshot({ path: filePath, fullPage: true });
};
