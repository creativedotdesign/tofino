import { test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { createHtmlReport } from 'axe-html-reporter';
import path from 'path';
import fs from 'fs';
import { processSitemap } from './utils/process-sitemap';

test.describe('Accessibility tests', () => {
  let urls: string[] = [];
  let totalPages = 0;
  const combinedResults: Array<{
    url: string;
    violations: any[];
    screenshot?: string;
  }> = [];

  test.beforeAll(async ({ baseURL }) => {
    const sitemapUrl = `${baseURL}/sitemap_index.xml`;
    // const sitemapUrl = `${baseURL}/page-sitemap.xml`;
    // const sitemapUrl = `${baseURL}/post-sitemap.xml`;
    urls = await processSitemap(sitemapUrl);
    totalPages = urls.length;

    const screenshotsDir = path.resolve('./test-results/screenshots');
    if (!fs.existsSync(screenshotsDir)) {
      fs.mkdirSync(screenshotsDir, { recursive: true });
    }
  });

  test('Should not have accessibility issues', async ({ page }) => {
    const viewports = [
      { name: 'mobile', width: 375, height: 667 },
      { name: 'tablet', width: 768, height: 1024 },
      { name: 'desktop', width: 1280, height: 720 },
    ];

    for (const viewport of viewports) {
      await test.step(`Viewport: ${viewport.name}`, async () => {
        await page.setViewportSize({ width: viewport.width, height: viewport.height });

        for (const url of urls) {
          await test.step(`${viewport.name} - ${url}`, async () => {
            await page.goto(url, { waitUntil: 'networkidle' });

            const results = await new AxeBuilder({ page })
              // Level A, and Level AA
              .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])

              // Level A only
              // .withTags(['wcag2a', 'wcag21a'])
              .analyze();

            console.log('URL is: ' + url);

            if (results.violations.length > 0) {
              // Capture screenshot
              const screenshotPath = path.resolve(
                './test-results/screenshots',
                `axe-violation-screenshot-${Date.now()}.png`
              );

              await page.screenshot({ path: screenshotPath, fullPage: true });

              combinedResults.push({
                url,
                violations: results.violations,
                screenshot: screenshotPath,
              });
            } else {
              console.log('Nothing found.');
            }
          });
        }
      });
    }
  });

  // After all tests, generate a single HTML report
  test.afterAll(async () => {
    const reportDir = 'accessibility-reports';
    const reportDirPath = path.resolve(process.cwd(), reportDir);

    // make sure the output folder exists
    if (!fs.existsSync(reportDirPath)) {
      fs.mkdirSync(reportDirPath, { recursive: true });
    }

    if (combinedResults.length > 0) {
      // Combine all results into one object
      const allResults = {
        urls: combinedResults.map((result) => result.url),
        violations: combinedResults.flatMap((result) => result.violations),
      };

      let screenshotSummary = '';

      // Add total pages scanned
      screenshotSummary += `<p>Scanned <strong>${totalPages}</strong> URLs</p>\n`;

      // Open <ul>
      screenshotSummary += '<ul>\n';

      for (let i = 0; i < combinedResults.length; i++) {
        const result = combinedResults[i];
        if (result.screenshot) {
          const relPath = path.relative(reportDirPath, result.screenshot);
          const name = path.basename(result.screenshot);

          // Add li
          screenshotSummary += `<li><a href="${relPath}">${i + 1}. ${name}</a></li>\n`;
        }
      }

      // Close </ul>
      screenshotSummary += '</ul>\n';

      // Generate the combined HTML report
      createHtmlReport({
        results: allResults,
        options: {
          outputDir: reportDir,
          reportFileName: 'combined-accessibility-report.html',
          customSummary: screenshotSummary,
        },
      });

      console.log(
        `Accessibility report saved at ${path.join(reportDir, 'combined-accessibility-report.html')}`
      );
    } else {
      console.log('No accessibility violations found across all tested URLs.');
    }
  });
});
