import { test, expect } from '@playwright/test';
import path from 'path';
import fs from 'fs';
import { detectOverflow } from './utils/overflow-check';
import { processSitemap } from './utils/process-sitemap';

test.describe('Overflow Detection Tests', () => {
  let urls: string[] = [];

  const overflowIssues: Array<{
    url: string;
    selectors: string[];
    screenshot?: string;
  }> = [];

  test.beforeAll(async ({ baseURL }) => {
    // Check for screenshots directory
    const screenshotsDir = path.resolve('./test-results/screenshots');
    if (!fs.existsSync(screenshotsDir)) {
      fs.mkdirSync(screenshotsDir, { recursive: true });
    }

    // Get all URLs from sitemap
    const sitemapUrl = `${baseURL}/sitemap_index.xml`;
    urls = await processSitemap(sitemapUrl);

    if (!urls.length) {
      throw new Error('No URLs found in the sitemap.');
    }

    console.log(`Found ${urls.length} URLs in the sitemap for overflow detection tests.`);
  });

  test('Check for overflowing elements on all sitemap URLs', async ({ page }) => {
    for (const url of urls) {
      try {
        // @TODO Mobile overflow
        // await page.setViewportSize({ width: 375, height: 812 });
        await page.goto(url, { waitUntil: 'networkidle' });

        // Call detectOverflow in the browser
        const result = await page.evaluate(detectOverflow);

        if (result.length > 0) {
          // Capture screenshot
          const screenshotPath = path.resolve(
            './test-results/screenshots',
            `overflow-detection-screenshot-${Date.now()}.png`
          );

          await page.screenshot({ path: screenshotPath, fullPage: true });

          // Push results to annotations meta data
          test.info().annotations.push({
            type: 'overflow-detection',
            description: JSON.stringify({
              url,
              selectors: result,
              screenshot: screenshotPath,
            }),
          });

          // Collect errors for reporting
          overflowIssues.push({
            url,
            selectors: result,
            screenshot: screenshotPath,
          });
          console.log(`Overflow issues on: ${url}`, result);

        } else {
          console.log(`No overflow issues on: ${url}`);
        }
      } catch (err) {
        console.error(`Error detecting overflow on ${url}: `, err);
      }
    }

    expect(overflowIssues.length, 'Some pages have overflowing elements').toBe(0);
  });
});
