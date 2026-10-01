import { test, expect } from '@playwright/test';
import { getAuditUrls } from '../support/utils/urls';
import { dismissOverlays } from '../support/utils/page';
import { excludes } from '../support/config';

/**
 * Keyboard focus audit. Tabs through each page and checks, for every stop:
 *
 *  - the element is on-screen (2.4.3): off-canvas carousel slides and
 *    collapsed panels must not receive focus;
 *  - focusing produced a visible change (2.4.7): an outline, box-shadow,
 *    colour, background, border or text-decoration differs from resting;
 *  - the element is not entirely covered by something else (2.4.11);
 *  - the element is not at opacity 0 when focus arrives (2.4.7), which is
 *    what reveal-on-scroll does to focusable content below the fold.
 *
 * Runs with animations ON and without scrolling first, so the page is in the
 * state a keyboard user meets it in.
 *
 * axe never moves focus, so none of this is covered by the axe run. Focus
 * indicator *contrast* (1.4.11) is not judged here; use the audit-ada-mcp
 * `--states` scan for that.
 */
/** Upper bound on Tab presses per page; a page with more stops is reported truncated. */
const MAX_STOPS = 200;

/** One tab stop and the problems found with it. */
type Stop = {
  index: number;
  selector: string;
  name: string;
  offscreen: boolean;
  noVisibleChange: boolean;
  obscured: boolean;
  insideHidden: boolean;
  /** Effective opacity below 0.2 when focus arrived (reveal-on-scroll holds, fades). */
  invisible: boolean;
  /** Inside a declared third-party container: recorded, never counted as a problem. */
  excluded: boolean;
};

/**
 * Inspects document.activeElement in the page. Runs via page.evaluate(), so it
 * has no imports and returns a plain object.
 *
 * @param exclude Third-party containers; a stop inside one is recorded but not judged.
 * @returns The stop's details, or null when focus is on the body (end of the tab order).
 */
const inspectActive = (exclude: string[]): Stop | null => {
  const el = document.activeElement as HTMLElement | null;
  if (!el || el === document.body) return null;

  /** Readable CSS path to the element, ids first, then classes and nth-of-type. */
  const selectorFor = (node: Element): string => {
    if (node.id) return `#${CSS.escape(node.id)}`;
    const cls = Array.from(node.classList)
      .slice(0, 2)
      .map((c) => `.${CSS.escape(c)}`)
      .join('');
    const parent = node.parentElement;
    const nth = parent
      ? Array.from(parent.children)
          .filter((s) => s.tagName === node.tagName)
          .indexOf(node) + 1
      : 1;
    const own = `${node.tagName.toLowerCase()}${cls}:nth-of-type(${nth})`;
    return parent && parent !== document.body ? `${selectorFor(parent)} > ${own}` : own;
  };

  /** Fingerprint of the computed properties a focus style can change. */
  const style = (node: Element): string => {
    const s = window.getComputedStyle(node);
    return [
      s.outlineStyle,
      s.outlineWidth,
      s.outlineColor,
      s.boxShadow,
      s.backgroundColor,
      s.color,
      s.borderColor,
      s.textDecorationLine,
      s.transform,
      s.opacity,
    ].join('|');
  };

  // Effective opacity the moment focus lands, before any reveal has played:
  // a focus ring on an element at opacity 0 is invisible to the user.
  let opacity = 1;
  for (
    let node: Element | null = el;
    node && node !== document.documentElement;
    node = node.parentElement
  ) {
    const value = Number.parseFloat(window.getComputedStyle(node).opacity);
    if (!Number.isNaN(value)) opacity *= value;
  }

  const rect = el.getBoundingClientRect();
  const offscreen =
    rect.width === 0 ||
    rect.height === 0 ||
    rect.right <= 0 ||
    rect.bottom <= 0 ||
    rect.left >= window.innerWidth ||
    rect.top >= window.innerHeight;

  // 2.4.11 (AA) fails only when the focused element is *entirely* hidden by
  // other content (sticky header, cookie banner). Sample the centre and the
  // four corners; every point must land on something else.
  let obscured = false;
  if (!offscreen) {
    const inset = 2;
    const points: Array<[number, number]> = [
      [rect.left + rect.width / 2, rect.top + rect.height / 2],
      [rect.left + inset, rect.top + inset],
      [rect.right - inset, rect.top + inset],
      [rect.left + inset, rect.bottom - inset],
      [rect.right - inset, rect.bottom - inset],
    ];
    obscured = points.every(([x, y]) => {
      if (x < 0 || y < 0 || x > window.innerWidth || y > window.innerHeight) return false;
      const hit = document.elementFromPoint(x, y);
      return !!hit && hit !== el && !el.contains(hit) && !hit.contains(el);
    });
  }

  const insideHidden = !!el.closest('[aria-hidden="true"], [inert]');
  const excluded = exclude.some((selector) => {
    try {
      return Array.from(document.querySelectorAll(selector)).some(
        (root) => root !== el && root.contains(el),
      );
    } catch {
      return false;
    }
  });

  const focusedStyle = style(el);
  // Resting state via blur/refocus (a class no stylesheet knows would not
  // change anything). Done LAST: everything geometric is measured while the
  // element is genuinely focused.
  el.blur();
  const restingStyle = style(el);
  el.focus({ preventScroll: true });
  // Blurring drops :focus-within on ancestors; a submenu shown that way is
  // display:none again and cannot be refocused. The tab walk still continues
  // from here (browsers keep the focus navigation start point), but the
  // indicator comparison is not meaningful for such an element.
  const refocused = document.activeElement === el;

  return {
    index: 0,
    selector: selectorFor(el),
    name: (el.getAttribute('aria-label') || el.textContent || '')
      .replace(/\s+/g, ' ')
      .trim()
      .slice(0, 60),
    offscreen,
    noVisibleChange: refocused && focusedStyle === restingStyle,
    obscured,
    insideHidden,
    invisible: opacity < 0.2,
    excluded,
  };
};

test.describe('Keyboard focus audit', () => {
  let urls: string[] = [];

  test.beforeAll(async ({ baseURL }) => {
    ({ urls } = await getAuditUrls(baseURL));
    console.log(`Focus audit: ${urls.length} URLs.`);
  });

  test('Every tab stop is visible, indicated and unobscured', async ({ page }) => {
    const failures: string[] = [];

    // One test walks every URL, so size its timeout to the crawl instead of
    // Playwright's per-test default (60s in playwright.config.ts).
    test.setTimeout(urls.length * 60_000);

    for (const url of urls) {
      await test.step(url, async () => {
        // Animations on and no scroll-through: this is the page as a keyboard
        // user meets it, with reveal-on-scroll content still held.
        await page.goto(url, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('load').catch(() => {});
        await page.waitForTimeout(300);
        await dismissOverlays(page);
        // Dismissing a banner leaves the sequential-focus start point where
        // its button was (often the end of the document). Focusing the body
        // resets it so the first Tab lands on the first focusable element.
        await page.evaluate(() => {
          (document.activeElement as HTMLElement | null)?.blur();
          document.body.setAttribute('tabindex', '-1');
          document.body.focus();
          document.body.removeAttribute('tabindex');
        });

        const stops: Stop[] = [];
        let firstKey = '';
        let lastKey = '';
        let stuck = 0;
        let stuckAt = '';

        for (let i = 0; i < MAX_STOPS; i += 1) {
          await page.keyboard.press('Tab');
          const stop = await page.evaluate(inspectActive, excludes);
          if (!stop) break;
          stop.index = stops.length + 1;
          const key = `${stop.selector}|${stop.name}`;
          if (stops.length && key === firstKey) break; // wrapped around to the first stop
          if (key === lastKey) {
            // Focus is not moving as far as this document can see: Tab is
            // inside a cross-origin <iframe>, or something is trapping it.
            stuck += 1;
            if (stuck > 10) {
              stuckAt = stop.selector;
              break;
            }
            continue;
          }
          stuck = 0;
          lastKey = key;
          if (!stops.length) firstKey = key;
          stops.push(stop);
        }

        const bad = stops.filter(
          (s) =>
            !s.excluded &&
            (s.offscreen || s.noVisibleChange || s.obscured || s.insideHidden || s.invisible),
        );
        test.info().annotations.push({
          type: 'focus-audit',
          description: JSON.stringify({ url, stops: stops.length, problems: bad }),
        });

        bad.forEach((s) => {
          const reasons = [
            s.offscreen && 'off-screen when focused (2.4.3)',
            s.noVisibleChange && 'no visible focus indicator (2.4.7)',
            s.obscured && 'covered by another element (2.4.11)',
            s.insideHidden && 'inside aria-hidden/inert (4.1.2)',
            s.invisible &&
              'invisible when focus arrives, opacity ~0 (2.4.7): reveal-on-scroll is holding focusable content',
          ].filter(Boolean);
          failures.push(
            `${url}\n  stop ${s.index} ${s.selector} "${s.name}": ${reasons.join(', ')}`,
          );
        });

        if (stuckAt && !/^iframe/.test(stuckAt)) {
          failures.push(
            `${url}\n  focus stopped moving at ${stuckAt} after 10 Tab presses (focus trap?)`,
          );
        }

        console.log(
          `Focus ${url}: ${stops.length} stops, ${bad.length} problems${stuckAt ? `, focus stayed on ${stuckAt}` : ''}`,
        );
      });
    }

    expect(failures.length, `Focus problems:\n${failures.join('\n')}`).toBe(0);
  });
});
