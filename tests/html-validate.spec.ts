import { test, expect } from '@playwright/test';
import { HtmlValidate } from 'html-validate';
import { parseStringPromise } from 'xml2js';

test.describe('HTML Validation Tests', () => {
  let urls: string[] = [];
  const validationErrors: Array<{ url: string; messages: any[] }> = [];

  test.beforeAll(async ({ baseURL }) => {
    const sitemapUrl = `${baseURL}/page-sitemap.xml`;

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

    console.log(`Found ${urls.length} URLs in the sitemap.`);
  });

  test('Validate HTML for all sitemap URLs', async ({ page, baseURL }) => {
    // Create an html-validate instance
    const htmlvalidate = new HtmlValidate();

    for (const url of urls) {
      try {
        await page.goto(url, { waitUntil: 'networkidle' });

        console.log("Pizaaaa!");
        console.log(`Validating URL: ${url}`);

        // Get the HTML content
        const html = await page.content();

        // Validate the HTML
        const report = await htmlvalidate.validateString(html);

        // Attach the results to the test metadata for the reporter
        if (!report.valid) {
          test.info().annotations.push({
            type: 'html-validation',
            description: JSON.stringify({
              url,
              results: report,
            }),
          });

          // Collect validation errors for reporting
          validationErrors.push({
            url,
            messages: report.results[0].messages,
          });

          console.warn(`HTML validation failed for ${url}`);
        } else {
          console.log(`HTML validation passed for ${url}`);
        }
      } catch (error) {
        console.error(`Error validating ${url}:`, error);
        validationErrors.push({
          url,
          messages: [{ message: error.message }],
        });
      }
    }

    expect(validationErrors.length, 'Some pages failed HTML validation').toBe(0);
  });
});