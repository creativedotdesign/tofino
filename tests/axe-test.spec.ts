import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import dotenv from 'dotenv';
import { parseStringPromise } from 'xml2js';
import { createHtmlReport } from 'axe-html-reporter';
import fs from 'fs';
import path from 'path';

// Load environment variables from .env file
dotenv.config();

test.describe('Accessibility tests', () => {
  let urls = [];
  const combinedResults = [];

  const baseUrl = process.env.VITE_ASSET_URL;

  test.beforeAll(async () => {
    const sitemapUrl = baseUrl + '/page-sitemap.xml';

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
      throw new Error('No URLs found in the sitemap.');
    }
  });

  test('Should not have accessibility issues', async ({ page }) => {
    for (const url of urls) {
      await page.goto(url);

      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
        .analyze();

      console.log('URL is: ' + url);

      if (results.violations.length > 0) {
        console.log('I found violations!');

        console.log(results.violations);

        combinedResults.push({
          url,
          violations: results.violations,
        });
      } else {
        console.log('Nothing found.');
      }
    }
  });

  // After all tests, generate a single HTML report
  test.afterAll(async () => {
    const reportDir = 'accessibility-reports';

    if (combinedResults.length > 0) {
      // Combine all results into one object
      const allResults = {
        urls: combinedResults.map((result) => result.url),
        violations: combinedResults.flatMap((result) => result.violations),
      };

      // Generate the combined HTML report
      createHtmlReport({
        results: allResults,
        options: {
          outputDir: reportDir,
          reportFileName: 'combined-accessibility-report.html',
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
