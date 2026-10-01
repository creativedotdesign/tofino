import { test, expect } from '@playwright/test';
import path from 'path';
import fs from 'fs';
import { detectOverflow } from '../support/utils/overflow-check';
import { getAuditUrls } from '../support/utils/urls';
import { capturePage, settlePage } from '../support/utils/page';
import { excludes, screenshots } from '../support/config';

test.describe('Overflow Detection Tests', () => {
  const outputDir = path.resolve('./tests/output');
  const screenshotsDir = path.join(outputDir, 'screenshots');
  let urls: string[] = [];

  const overflowIssues: Array<{
    url: string;
    selectors: string[];
    screenshot?: string;
  }> = [];

  // 320 is the WCAG 1.4.10 reflow width; desktop catches fixed-width media.
  const widths = [
    { name: 'reflow-320', width: 320, height: 640 },
    { name: 'mobile', width: 375, height: 812 },
    { name: 'desktop', width: 1280, height: 720 },
  ];

  test.beforeAll(async ({ baseURL }) => {
    // Check for screenshots directory
    if (!fs.existsSync(screenshotsDir)) {
      fs.mkdirSync(screenshotsDir, { recursive: true });
    }

    ({ urls } = await getAuditUrls(baseURL));

    if (!urls.length) {
      throw new Error('No URLs found in the sitemap.');
    }

    console.log(`Found ${urls.length} URLs in the sitemap for overflow detection tests.`);
  });

  test('Check for overflowing elements on all sitemap URLs', async ({ page }) => {
    // One test walks every URL, so size its timeout to the crawl instead of
    // Playwright's per-test default (60s in playwright.config.ts).
    test.setTimeout(urls.length * widths.length * 30_000);

    for (const size of widths) {
      await page.setViewportSize({ width: size.width, height: size.height });

      for (const url of urls) {
        try {
          await page.goto(url, { waitUntil: 'domcontentloaded' });
          await settlePage(page);

          // Call detectOverflow in the browser
          const result = await page.evaluate(detectOverflow, excludes);

          if (result.length > 0) {
            // Screenshots are opt-in (A11Y_SCREENSHOTS=1). detectOverflow() has
            // already outlined the offending elements in red.
            let screenshotPath: string | undefined;
            if (screenshots) {
              screenshotPath = path.join(screenshotsDir, `overflow-${size.name}-${Date.now()}.png`);
              await capturePage(page, screenshotPath);
            }

            // Push results to annotations meta data
            test.info().annotations.push({
              type: 'overflow-detection',
              description: JSON.stringify({
                url: `${url} (${size.name})`,
                selectors: result,
                screenshot: screenshotPath,
              }),
            });

            // Collect errors for reporting
            overflowIssues.push({
              url: `${url} (${size.name})`,
              selectors: result,
              screenshot: screenshotPath,
            });
            console.log(`Overflow issues on: ${url} (${size.name})`, result);
          } else {
            console.log(`No overflow issues on: ${url} (${size.name})`);
          }
        } catch (err) {
          console.error(`Error detecting overflow on ${url}: `, err);
        }
      }
    }

    expect(overflowIssues.length, 'Some pages have overflowing elements').toBe(0);
  });
});
