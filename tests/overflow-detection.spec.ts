import { test, expect } from '@playwright/test';
import { parseStringPromise } from 'xml2js';
import path from 'path';
import fs from 'fs';
import { detectOverflow } from './utils/overflow-check';

test.describe('Overflow Detection Tests', () => {
  let urls: string[] = [];

  const overflowIssues: Array<{
    url: string;
    selectors: string[];
    screenshot?: string;
  }> = [];

  test.beforeAll(async ({ baseURL }) => {
    const sitemapUrl = `${baseURL}/page-sitemap.xml`;

    // Check for screenshots directory
    const screenshotsDir = path.resolve('./test-results/screenshots');
    if (!fs.existsSync(screenshotsDir)) {
      fs.mkdirSync(screenshotsDir, { recursive: true });
    }

    // Fetch the sitemap content
    const response = await fetch(sitemapUrl);

    if (!response.ok) {
      throw new Error(`Failed to fetch sitemap: ${response.status}`);
    }

    // Read the text content of the sitemap
    const xmlContent = await response.text();

    // Parse the XML content to extract URLs
    const parsedSitemap = await parseStringPromise(xmlContent);
    urls = parsedSitemap.urlset.url.map((entry: any) => entry.loc[0]);

    if (!urls.length) {
      throw new Error('No URLs found in the sitemap for overflow detection tests.');
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
