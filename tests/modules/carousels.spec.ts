import { test, expect } from '@playwright/test';
import { carousel, tabbableSelector } from '../support/config';
import { getAuditUrls } from '../support/utils/urls';
import { dismissOverlays } from '../support/utils/page';

/**
 * Carousel audit. For every Swiper instance on the crawled pages:
 *
 *  - prev/next are real <button>s with names that don't say "button",
 *    and Previous comes before Next in the DOM (2.4.3);
 *  - after advancing, slides that are not shown are removed from the tab
 *    order (inert, aria-hidden, or no tabbable content) (2.4.3);
 *  - nothing tabbable sits inside an aria-hidden slide (4.1.2);
 *  - the carousel region is labelled (aria-roledescription or aria-label
 *    on the root or its section) (best practice).
 *
 * Swiper only does part of this even with its A11y module; the inert
 * handling is the theme's job (see facts-rankings/script.ts).
 */
test.describe('Carousels', () => {
  let urls: string[] = [];

  test.beforeAll(async ({ baseURL }) => {
    ({ urls } = await getAuditUrls(baseURL));
  });

  test('Swiper instances are keyboard and screen-reader safe', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 800 });
    const failures: string[] = [];
    let checked = 0;

    // One test walks every URL, so size its timeout to the crawl instead of
    // Playwright's per-test default (60s in playwright.config.ts).
    test.setTimeout(urls.length * 30_000);

    for (const url of urls) {
      await page.goto(url, { waitUntil: 'domcontentloaded' });
      await page.waitForLoadState('load').catch(() => {});
      await page.waitForTimeout(300);
      await dismissOverlays(page);

      // One carousel = one module/section, even when it is built from two
      // synced Swipers (text + media). Tag each unique scope once.
      const count = await page.evaluate((rootSel) => {
        const scopes = new Set<Element>();
        document.querySelectorAll(rootSel).forEach((root) => {
          if ((root as HTMLElement).offsetParent === null) return;
          scopes.add(root.closest('[data-module], section, .module') ?? root.parentElement ?? root);
        });
        [...scopes].forEach((scope, i) => scope.setAttribute('data-carousel-scope', String(i)));
        return scopes.size;
      }, carousel.root);
      if (!count) continue;

      for (let i = 0; i < count; i += 1) {
        const scope = await page.$(`[data-carousel-scope="${i}"]`);
        if (!scope) continue;
        checked += 1;
        const label = `${url} carousel #${i + 1}`;

        const info = await scope.evaluate((el, sel) => {
          const prev = el.querySelector(sel.prev);
          const next = el.querySelector(sel.next);
          const name = (n: Element | null) =>
            n
              ? (n.getAttribute('aria-label') || n.textContent || '').replace(/\s+/g, ' ').trim()
              : '';
          // A module may run two synced Swipers (text + aria-hidden media);
          // the label belongs on the one exposed to assistive technology.
          const attrs = ['aria-roledescription', 'aria-label', 'aria-labelledby'];
          const labelled =
            attrs.some((a) => el.hasAttribute(a)) ||
            Array.from(el.querySelectorAll(sel.root)).some(
              (root) =>
                root.getAttribute('aria-hidden') === 'true' ||
                !!root.closest('[aria-hidden="true"]') ||
                attrs.some((a) => root.hasAttribute(a)),
            );
          return {
            hasControls: !!prev && !!next,
            prevIsButton: prev?.tagName === 'BUTTON',
            nextIsButton: next?.tagName === 'BUTTON',
            prevName: name(prev),
            nextName: name(next),
            prevBeforeNext:
              !!prev &&
              !!next &&
              !!(prev.compareDocumentPosition(next) & Node.DOCUMENT_POSITION_FOLLOWING),
            labelled,
            slides: el.querySelectorAll(sel.slide).length,
          };
        }, carousel);

        if (!info.labelled)
          failures.push(
            `${label}: carousel has no aria-roledescription / aria-label (Swiper A11y module or a section label).`,
          );

        if (info.hasControls) {
          if (!info.prevIsButton || !info.nextIsButton)
            failures.push(`${label}: prev/next are not <button> elements.`);
          if (!info.prevName || !info.nextName)
            failures.push(`${label}: prev/next lack accessible names.`);
          if (/\b(button|posts?)\b/i.test(`${info.prevName} ${info.nextName}`))
            failures.push(
              `${label}: control names "${info.prevName}" / "${info.nextName}" should be "Previous" / "Next" (+ what, e.g. "slide"), never "button".`,
            );
          if (!info.prevBeforeNext) failures.push(`${label}: Next precedes Previous in the DOM.`);

          // Advance once, then check the hidden slides.
          const next = scope.$(carousel.next);
          await (await next)?.click({ force: true }).catch(() => {});
          await page.waitForTimeout(700);
        } else if (info.slides > 1) {
          failures.push(
            `${label}: ${info.slides} slides but no prev/next controls found (check tests/support/config.ts selectors).`,
          );
        }

        const hidden = await scope.evaluate(
          (el, sel) => {
            const problems: string[] = [];
            el.querySelectorAll(sel.slide).forEach((slide, n) => {
              const shown = slide.matches(sel.activeSlide);
              const removed =
                slide.hasAttribute('inert') || slide.getAttribute('aria-hidden') === 'true';
              const tabbables = Array.from(slide.querySelectorAll(sel.tabbable)).filter((t) => {
                const s = window.getComputedStyle(t);
                return (
                  s.display !== 'none' &&
                  s.visibility !== 'hidden' &&
                  t.getAttribute('tabindex') !== '-1'
                );
              });
              if (!shown && !removed && tabbables.length) {
                problems.push(
                  `slide ${n + 1} is off-screen but keeps ${tabbables.length} tabbable element(s); set inert on inactive slides`,
                );
              }
              if (
                slide.getAttribute('aria-hidden') === 'true' &&
                !slide.hasAttribute('inert') &&
                tabbables.length
              ) {
                problems.push(`slide ${n + 1} is aria-hidden but still tabbable`);
              }
            });
            return problems;
          },
          { ...carousel, tabbable: tabbableSelector },
        );
        hidden.forEach((p) => failures.push(`${label}: ${p}`));
      }
    }

    test
      .info()
      .annotations.push({ type: 'carousels', description: `${checked} carousel(s) checked` });
    expect(failures.length, `Carousel problems:\n${failures.join('\n')}`).toBe(0);
  });
});
