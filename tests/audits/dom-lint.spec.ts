import { test, expect } from '@playwright/test';
import path from 'path';
import fs from 'fs';
import { domLint, type DomLintFinding } from '../support/utils/dom-lint';
import { getAuditUrls } from '../support/utils/urls';
import { capturePage } from '../support/utils/page';
import { excludes, screenshots } from '../support/config';

/**
 * DOM lint audit: markup patterns axe cannot judge (breadcrumb lists,
 * redundant landmark labels, unhidden decorative SVGs, "opens in new window",
 * generic link text, card grids without list semantics, heading skips, ...).
 * See tests/support/utils/dom-lint.ts for the rule list and the WCAG mapping.
 *
 * `error` findings fail the run; `review` findings are reported only.
 */
test.describe('DOM lint', () => {
  const outputDir = path.resolve('./tests/output');
  const screenshotsDir = path.join(outputDir, 'screenshots');
  let urls: string[] = [];

  const errors: Array<{ url: string; findings: DomLintFinding[] }> = [];
  const allFindings: Array<{ url: string; findings: DomLintFinding[] }> = [];

  test.beforeAll(async ({ baseURL }) => {
    fs.mkdirSync(screenshotsDir, { recursive: true });
    ({ urls } = await getAuditUrls(baseURL));
    console.log(`DOM lint: ${urls.length} URLs.`);
  });

  test('Markup patterns on all sitemap URLs', async ({ page }) => {
    // One test walks every URL, so size its timeout to the crawl instead of
    // Playwright's per-test default (60s in playwright.config.ts).
    test.setTimeout(urls.length * 30_000);

    for (const url of urls) {
      await test.step(url, async () => {
        // Animations stay on: the lint reads the DOM (opacity is irrelevant)
        // and rule 13 needs the hero video to have started.
        await page.goto(url, { waitUntil: 'domcontentloaded' });
        await page.waitForLoadState('load').catch(() => {});
        await page.waitForTimeout(500);

        const findings = await page.evaluate(domLint, excludes);
        const excludedRegions = excludes.length
          ? await page.locator(excludes.join(', ')).count()
          : 0;
        if (excludedRegions) {
          console.log(`DOM lint ${url}: ${excludedRegions} third-party region(s) left out.`);
        }

        if (!findings.length) {
          console.log(`DOM lint clean: ${url}`);
          return;
        }

        // Screenshots are opt-in (A11Y_SCREENSHOTS=1); the report lists every selector.
        let screenshotPath: string | undefined;
        if (screenshots) {
          screenshotPath = path.join(screenshotsDir, `dom-lint-${Date.now()}.png`);
          await capturePage(
            page,
            screenshotPath,
            findings.map((f) => f.selector),
          );
        }

        test.info().annotations.push({
          type: 'dom-lint',
          description: JSON.stringify({ url, findings, screenshot: screenshotPath }),
        });

        allFindings.push({ url, findings });
        const errorFindings = findings.filter((f) => f.level === 'error');
        if (errorFindings.length) {
          errors.push({ url, findings: errorFindings });
        }

        console.log(
          `DOM lint ${url}: ${errorFindings.length} errors, ${findings.length - errorFindings.length} to review`,
        );
      });
    }

    // Machine-readable copy of every finding, for tooling and quick greps.
    fs.writeFileSync(path.join(outputDir, 'dom-lint.json'), JSON.stringify(allFindings, null, 2));

    const summary = errors
      .map(
        ({ url, findings }) =>
          `${url}\n${findings.map((f) => `  [${f.rule}] ${f.selector}: ${f.detail}`).join('\n')}`,
      )
      .join('\n\n');

    expect(errors.length, `DOM lint errors on ${errors.length} page(s):\n${summary}`).toBe(0);
  });
});
