import { test, expect, type Page } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { createHtmlReport } from 'axe-html-reporter';
import path from 'path';
import fs from 'fs';
import type { Result } from 'axe-core';
import { getAuditUrls } from '../support/utils/urls';
import { capturePage, dismissOverlays, settlePage } from '../support/utils/page';
import { axeTags, excludes, hoverPass, overlays, screenshots, viewports } from '../support/config';

/**
 * axe audit. Every sitemap URL (see tests/support/utils/urls.ts) at each
 * viewport in tests/support/config.ts, with the WCAG 2.2 AA and best-practice
 * tag sets. Beyond the resting page it also runs axe:
 *
 *  - inside each overlay in the config (menu, search) once opened;
 *  - on every hovered link/button at desktop when A11Y_HOVER=1.
 *
 * `incomplete` results (contrast over images, link purpose) are kept in the
 * report for manual review. Any violation fails the run. Output:
 * tests/output/accessibility-report.html with screenshots of flagged nodes.
 */

/** An axe result stamped with the state and viewport it was captured in, for the report. */
type ReportedResult = Result & {
  reportId?: number;
  state?: string;
  viewport?: string;
};

/** axe output for one URL in one viewport and page state. */
type PageResult = {
  url: string;
  violations: ReportedResult[];
  incomplete?: Result[];
  screenshot?: string;
  viewport: string;
  state?: string;
};

/**
 * axe builder for this suite: the WCAG 2.2 AA + best-practice tags, no
 * injection into frames (embed content belongs to its provider; the <iframe>
 * element itself is still checked), and the declared third-party exclusions.
 */
const axe = (page: Page): AxeBuilder => {
  let builder = new AxeBuilder({ page }).withTags(axeTags).options({ iframes: false });
  for (const selector of excludes) {
    builder = builder.exclude(selector);
  }
  return builder;
};

test.describe('Accessibility tests', () => {
  const outputDir = path.resolve('./tests/output');
  const screenshotsDir = path.join(outputDir, 'screenshots');
  const reportPath = path.join(outputDir, 'accessibility-report.html');
  let urls: string[] = [];
  let sitemapCounts: Record<string, number> = {};
  const combinedResults: PageResult[] = [];

  test.beforeAll(async ({ baseURL }) => {
    // Get sitemap index
    const { urls: allUrls, counts } = await getAuditUrls(baseURL);
    urls = allUrls;
    sitemapCounts = counts;
    console.log(
      `Accessibility audit: ${urls.length} URLs x ${viewports.length} viewports, tags ${axeTags.join(', ')}.`,
    );

    if (!fs.existsSync(screenshotsDir)) {
      fs.mkdirSync(screenshotsDir, { recursive: true });
    }
  });

  test('Should not have accessibility issues', async ({ page }) => {
    // One test walks every URL, so size its timeout to the crawl instead of
    // Playwright's per-test default (60s in playwright.config.ts).
    test.setTimeout(urls.length * viewports.length * 120_000);

    for (const viewport of viewports) {
      await test.step(`Viewport: ${viewport.name}`, async () => {
        await page.setViewportSize({ width: viewport.width, height: viewport.height });

        for (const url of urls) {
          await test.step(`${viewport.name} - ${url}`, async () => {
            await page.goto(url, { waitUntil: 'domcontentloaded' });
            // Scroll through, pin reveal-on-scroll content, wait for media.
            await settlePage(page);

            // WCAG 2.2 AA + best-practice (heading order, landmark labels,
            // list semantics) — see tests/support/config.ts.
            const results = await axe(page).analyze();

            // `incomplete` = axe could not decide (contrast over images and
            // gradients, most link-purpose checks). Report them for manual
            // review instead of dropping them.
            if (results.incomplete.length > 0) {
              combinedResults.push({
                url,
                violations: [],
                incomplete: results.incomplete,
                viewport: viewport.name,
                state: 'incomplete',
              });
            }

            console.log(
              `axe ${viewport.name} ${url}: ${results.violations.length} violations` +
                (results.violations.length
                  ? ` (${results.violations.map((v) => v.id).join(', ')})`
                  : '') +
                `, ${results.incomplete.length} incomplete`,
            );

            if (results.violations.length > 0) {
              // Screenshots are opt-in (A11Y_SCREENSHOTS=1); the report already
              // carries each node's selector and HTML.
              let screenshotPath: string | undefined;
              if (screenshots) {
                screenshotPath = path.join(
                  screenshotsDir,
                  `axe-${viewport.name}-${Date.now()}.png`,
                );
                await capturePage(
                  page,
                  screenshotPath,
                  results.violations.flatMap((v) => v.nodes.map((n) => n.target.join(' '))),
                );
              }

              combinedResults.push({
                url,
                violations: results.violations,
                screenshot: screenshotPath,
                viewport: viewport.name,
                state: 'initial',
              });
            } else {
              console.log('Nothing found.');
            }

            // Overlay states: open each menu/dialog present and re-run axe on
            // it. Menu and modal findings only exist in this state. The
            // resting audit above kept banners in place (they must be
            // accessible too); the cookie notice is a modal <dialog>, so it
            // is dismissed here or it blocks every trigger behind it.
            let dismissed = false;
            for (const overlay of overlays) {
              const trigger = page.locator(overlay.trigger).first();
              if (!(await trigger.count()) || !(await trigger.isVisible())) continue;

              await test.step(`State: ${overlay.name} open`, async () => {
                if (!dismissed) {
                  await dismissOverlays(page);
                  dismissed = true;
                }
                // Keyboard open, as in the overlays spec: Safari does not
                // focus a clicked button, and a sticky header can sit over
                // the trigger at narrow widths.
                try {
                  await trigger.scrollIntoViewIfNeeded({ timeout: 5_000 });
                  await trigger.focus({ timeout: 5_000 });
                  await trigger.press('Enter', { timeout: 5_000 });
                } catch (error) {
                  console.warn(
                    `${overlay.name}: could not open on ${url} at ${viewport.name} (${error instanceof Error ? error.message.split('\n')[0] : error})`,
                  );
                  return;
                }
                await page.waitForTimeout(400);
                // Content fetched after opening (a quiz) flags itself busy.
                await page
                  .locator(overlay.container)
                  .locator('[aria-busy="true"]')
                  .first()
                  .waitFor({ state: 'detached', timeout: 10_000 })
                  .catch(() => {});

                const stateResults = await axe(page).include(overlay.container).analyze();

                if (stateResults.violations.length > 0) {
                  let screenshotPath: string | undefined;
                  if (screenshots) {
                    screenshotPath = path.join(
                      screenshotsDir,
                      `axe-state-${overlay.name.replace(/[^a-z0-9]/gi, '_')}-${Date.now()}.png`,
                    );
                    await page.screenshot({ path: screenshotPath });
                  }
                  combinedResults.push({
                    url,
                    violations: stateResults.violations,
                    screenshot: screenshotPath,
                    viewport: viewport.name,
                    state: `${overlay.name} open`,
                  });
                }

                await page.keyboard.press('Escape');
                await page.waitForTimeout(200);
              });
            }

            // Hover pass (opt-in: A11Y_HOVER=1), desktop only. Hovers each
            // distinct visible link/button once and runs axe on it for
            // hover-state contrast. Header and footer are hovered on the first
            // URL only, since they repeat on every page. audit-ada-mcp
            // --states covers hover and focus contrast faster and in more
            // depth; keep this for spot checks.
            if (viewport.name === 'desktop' && hoverPass) {
              const includeChrome = url === urls[0];
              const targets = await page.$$(
                'button, a[href], [role="button"], [role="link"], [tabindex]:not([tabindex="-1"])',
              );
              let nextId = combinedResults.length + 1;
              let hovered = 0;

              for (const el of targets) {
                const info = await el.evaluate((node, chrome) => {
                  const element = node as HTMLElement;
                  const visible =
                    element.offsetParent !== null && !(element as HTMLButtonElement).disabled;
                  const inChrome = !!element.closest('header, footer');
                  const classes = Array.from(element.classList).slice(0, 2).join('.');
                  return {
                    skip: !visible || (inChrome && !chrome),
                    label: `${element.tagName.toLowerCase()}${classes ? `.${classes}` : ''}`,
                  };
                }, includeChrome);
                if (info.skip) continue;

                try {
                  await el.scrollIntoViewIfNeeded({ timeout: 2000 });
                  await el.hover({ force: true, timeout: 2000 });
                } catch {
                  continue;
                }
                await page.waitForTimeout(150);
                hovered += 1;

                // axe needs a selector to scope to: tag the element for the run.
                await el.evaluate((node) => node.setAttribute('data-a11y-hover', ''));
                const hoverResults = await axe(page).include('[data-a11y-hover]').analyze();
                await el.evaluate((node) => node.removeAttribute('data-a11y-hover'));

                if (hoverResults.violations.length > 0) {
                  combinedResults.push({
                    url,
                    violations: hoverResults.violations.map((v) => ({
                      ...v,
                      reportId: nextId++,
                      state: `hover ${info.label}`,
                      viewport: viewport.name,
                    })),
                    viewport: viewport.name,
                    state: 'hover',
                  });
                }
              }

              console.log(`Hover pass ${url}: ${hovered} elements.`);
            }
          });
        }
      });
    }
  });

  test.afterEach(() => {
    const violationCount = combinedResults.reduce((n, r) => n + r.violations.length, 0);
    expect(
      violationCount,
      `axe reported ${violationCount} violation(s); see tests/output/accessibility-report.html`,
    ).toBe(0);
  });

  // After all tests, generate a single HTML report
  test.afterAll(async () => {
    if (!fs.existsSync(outputDir)) {
      fs.mkdirSync(outputDir, { recursive: true });
    }

    const violationCount = combinedResults.reduce((n, r) => n + r.violations.length, 0);
    const incompleteCount = combinedResults.reduce((n, r) => n + (r.incomplete?.length ?? 0), 0);
    console.log(`axe: ${violationCount} violations, ${incompleteCount} incomplete (needs review).`);

    if (combinedResults.length > 0) {
      // Combine all results into one object
      const allResults = {
        urls: combinedResults.map((result) => result.url),
        violations: combinedResults.flatMap((result) => result.violations),
        incomplete: combinedResults.flatMap((result) => result.incomplete ?? []),
      };

      let screenshotSummary = '';

      // Get sitemap + viewport info
      for (const viewport of viewports) {
        for (const sitemapUrl of Object.keys(sitemapCounts)) {
          screenshotSummary += `<p>${sitemapCounts[sitemapUrl]} items scanned from ${sitemapUrl} at ${viewport.name}</p>\n`;
        }
      }

      // Generate the combined HTML report
      createHtmlReport({
        results: allResults,
        options: {
          // axe-html-reporter joins this onto process.cwd(); an absolute path
          // would nest the whole path under the theme directory.
          outputDir: path.relative(process.cwd(), outputDir),
          reportFileName: 'accessibility-report.html',
          customSummary: screenshotSummary,
        },
      });

      if (!fs.existsSync(reportPath)) {
        console.log('axe-html-reporter wrote no report (no violations to render).');
        return;
      }

      console.log(`Accessibility report saved at ${reportPath}`);

      // Inline injection of screenshots, viewport and url
      let reportHtml = fs.readFileSync(reportPath, 'utf8');

      combinedResults.forEach((result, resultIndex) => {
        if (!result.screenshot) return;

        result.violations.forEach((violation) => {
          // Try to find the matching violation by Axe rule ID
          const ruleId = violation.id;
          const idNumber = resultIndex + 1;

          console.log(`Injecting screenshot for violation ${idNumber} (${ruleId})`);

          // Regex to match the <a id="X"> anchor and its violation card
          const regex = new RegExp(`(<a id="${idNumber}">[\\s\\S]*?<\\/div>)`, 'g');

          const imgTag = `
            <p>
              <strong>Breakpoint:</strong> ${result.viewport}<br>
              <strong>URL:</strong> <a href="${result.url}" target="_blank" rel="noopener noreferrer">${result.url}</a><br>
              <strong>State:</strong> ${result.state}
            </p>

            <img src="${path.relative(outputDir, result.screenshot)}"
              alt="Screenshot for violation ${idNumber}"
              style="max-width: 100%; margin:1rem 0;"
            />`;

          // Replace and inject
          reportHtml = reportHtml.replace(regex, (match) => {
            console.log(`Found match for ID ${idNumber}, inserting details.`);
            return match + imgTag;
          });
        });
      });

      // Save the updated HTML
      fs.writeFileSync(reportPath, reportHtml, 'utf8');
      console.log(`Final report at: ${reportPath}`);
    } else {
      console.log('No accessibility violations found across all tested URLs.');
    }
  });
});
