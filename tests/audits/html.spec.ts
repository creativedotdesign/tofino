import { test, expect } from '@playwright/test';
import { HtmlValidate, type Message } from 'html-validate';
import path from 'path';
import fs from 'fs';
import { getAuditUrls } from '../support/utils/urls';
import { capturePage } from '../support/utils/page';
import { excludes, screenshots } from '../support/config';

test.describe('HTML Validation Tests', () => {
  const outputDir = path.resolve('./tests/output');
  const screenshotsDir = path.join(outputDir, 'screenshots');
  let urls: string[] = [];

  /** html-validate message plus the surrounding source lines for the report. */
  type ValidationMessage = Partial<Message> & { message: string; snippet?: string };

  const validationErrors: Array<{
    url: string;
    messages: ValidationMessage[];
    screenshot?: string;
  }> = [];

  test.beforeAll(async ({ baseURL }) => {
    // Check for screenshots directory
    if (!fs.existsSync(screenshotsDir)) {
      fs.mkdirSync(screenshotsDir, { recursive: true });
    }

    // Get all URLs from sitemap
    ({ urls } = await getAuditUrls(baseURL));

    if (!urls.length) {
      throw new Error('No URLs found in the sitemap.');
    }

    console.log(`Found ${urls.length} URLs in the sitemap for html validation tests.`);
  });

  test('Validate HTML for all sitemap URLs', async ({ page }) => {
    // One test walks every URL, so size its timeout to the crawl instead of
    // Playwright's per-test default (60s in playwright.config.ts).
    test.setTimeout(urls.length * 30_000);

    // Create an html-validate instance
    const htmlvalidate = new HtmlValidate({
      extends: ['html-validate:recommended'],
      rules: {
        'no-inline-style': 'off',

        // page.content() serialises the live DOM, so boolean attributes come
        // back as hidden="" and whitespace is normalised. These three rules
        // only ever report that serialisation, never the template.
        'attribute-boolean-style': 'off',
        'attribute-empty-style': 'off',
        'no-trailing-whitespace': 'off',

        // Accessibility rules (off in `recommended`). WCAG techniques:
        // H30 link text, H32 submit button, H36 image submit alt, H37 img alt,
        // H63 table headers, H67 decorative images, H71 fieldset/legend.
        'wcag/h30': 'error',
        'wcag/h32': 'error',
        'wcag/h36': 'error',
        'wcag/h37': 'error',
        'wcag/h63': 'error',
        'wcag/h67': 'error',
        'wcag/h71': 'error',
        'no-redundant-role': 'error',
        'no-redundant-aria-label': 'error',
        'prefer-native-element': 'error',
        'no-implicit-button-type': 'error',
        'empty-heading': 'error',
        'heading-level': 'error',
        'hidden-focusable': 'error',
        'input-missing-label': 'error',
        'multiple-labeled-controls': 'error',
        // focusable="false" only mattered for IE11; hundreds of warnings, no value.
        'svg-focusable': 'off',
        // Wants non-breaking hyphens in phone numbers so they never wrap. The
        // Carlsbad audit found VoiceOver reads U+2011 numbers as one integer,
        // and DOM lint flags them (tel-nonbreaking-hyphen); layout wins with
        // white-space: nowrap on the link instead.
        'tel-non-breaking': 'off',
        'area-alt': 'error',
        'aria-label-misuse': 'error',
        'no-autoplay': 'warn',
        'text-content': 'error',
      },
    });

    for (const url of urls) {
      try {
        await page.goto(url, { waitUntil: 'domcontentloaded' });
        // Wait a bit for dynamic content to load
        await page.waitForTimeout(1000);

        // Get the HTML content
        // Serialise the DOM with third-party embed contents removed (their
        // markup is the provider's to fix); the containers themselves stay.
        const html = await page.evaluate((exclude) => {
          const root = document.documentElement.cloneNode(true) as HTMLElement;
          exclude.forEach((selector) => {
            root.querySelectorAll(selector).forEach((el) => {
              el.innerHTML = '';
            });
          });
          return `<!DOCTYPE html>${root.outerHTML}`;
        }, excludes);

        // Validate the HTML
        const report = await htmlvalidate.validateString(html);

        // If errors add a code snippet
        if (!report.valid && report.results?.[0]?.messages) {
          const lines = html.split('\n');

          // Map over messages to add a snippet
          report.results[0].messages = report.results[0].messages.map((msg: Message) => {
            if (msg.line && lines[msg.line - 1]) {
              // Get multiple lines
              const start = Math.max(0, msg.line - 2); // one line before
              const end = Math.min(lines.length, msg.line + 1); // one line after
              const snippet = lines.slice(start, end).join('\n');

              return { ...msg, snippet };
            }
            return msg;
          });
        }

        // Attach the results to the test metadata for the reporter
        if (!report.valid) {
          // Screenshots are opt-in (A11Y_SCREENSHOTS=1); the report carries selectors and snippets.
          let screenshotPath: string | undefined;
          if (screenshots) {
            screenshotPath = path.join(screenshotsDir, `html-validation-${Date.now()}.png`);
            await capturePage(page, screenshotPath);
          }

          // Push results to annotations meta data
          test.info().annotations.push({
            type: 'html-validation',
            description: JSON.stringify({
              url,
              results: report,
              screenshot: screenshotPath,
            }),
          });

          // Collect validation errors for reporting
          validationErrors.push({
            url,
            messages: report.results[0].messages,
            screenshot: screenshotPath,
          });

          console.warn(`HTML validation failed for ${url}`);
        } else {
          console.log(`HTML validation passed for ${url}`);
        }
      } catch (error) {
        console.error(`Error validating html on ${url}:`, error);
        validationErrors.push({
          url,
          messages: [{ message: error instanceof Error ? error.message : String(error) }],
        });
      }
    }

    expect(validationErrors.length, 'Some pages failed HTML validation').toBe(0);
  });
});
