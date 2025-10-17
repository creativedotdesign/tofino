import { test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { createHtmlReport } from 'axe-html-reporter';
import path from 'path';
import fs from 'fs';
import { processSitemap } from './utils/process-sitemap';

const viewports = [
  { name: 'mobile', width: 375, height: 667 },
  { name: 'tablet', width: 768, height: 1024 },
  { name: 'desktop', width: 1280, height: 720 },
];

test.describe('Accessibility tests', () => {
  let urls: string[] = [];
  let sitemapCounts: Record<string, number> = {};
  const combinedResults: Array<{
    url: string;
    violations: any[];
    screenshot?: string;
    viewport: string;
    state?: string;
  }> = [];

  test.beforeAll(async ({ baseURL }) => {
    // Get sitemap index
    const { urls: allUrls, counts } = await processSitemap(
      `${baseURL}/sitemap_index.xml`
    );
    urls = allUrls;
    sitemapCounts = counts;

    const screenshotsDir = path.resolve('./test-results/screenshots');
    if (!fs.existsSync(screenshotsDir)) {
      fs.mkdirSync(screenshotsDir, { recursive: true });
    }
  });

  test('Should not have accessibility issues', async ({ page }) => {
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
              // Add ada-violation class to highlight each offending node
              for (const violation of results.violations) {
                for (const node of violation.nodes) {
                  const selector = node.target.join(' ');
                  await page.$eval(selector, el => el.classList.add('ada-violation')).catch(() => { });
                }
              }

              // Capture screenshot
              const screenshotPath = path.resolve(
                './test-results/screenshots',
                `axe-violation-screenshot-${Date.now()}.png`
              );
              await page.screenshot({ path: screenshotPath, fullPage: true });

              // Remove ada-violation highlight
              for (const violation of results.violations) {
                for (const node of violation.nodes) {
                  const selector = node.target.join(' ');
                  await page.$eval(selector, el => el.classList.remove('ada-violation')).catch(() => { });
                }
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

            // Hover tests, desktop only
            if (viewport.name === 'desktop') {
              // Find hoverable elements
              const hoverSelectors = await page.$$eval(
                'button, a, [role="button"], [role="link"], [tabindex]',
                elements =>
                  elements
                    .filter(el => el.offsetParent !== null && !el.disabled)
                    .map(el => {
                      if (el.id) return `#${CSS.escape(el.id)}`;
                      if (el.className) {
                        return (
                          '.' +
                          el.className
                            .trim()
                            .split(/\s+/)
                            .map(cls => CSS.escape(cls))
                            .join('.')
                        );
                      }
                      return el.tagName.toLowerCase();
                    })
              );

              console.log(`Found ${hoverSelectors.length} hoverable elements.`);
              let nextId = combinedResults.length + 1;

              // Hover each and test
              for (const selector of hoverSelectors) {
                await test.step(`Hover: ${selector}`, async () => {
                  console.log(`Hovering: ${selector}`);

                  const el = await page.$(selector);
                  if (!el) {
                    console.log(`Element ${selector} not found — skipping.`);
                    return;
                  }

                  await el.hover();
                  await page.waitForTimeout(300);

                  const hoverResults = await new AxeBuilder({ page })
                    .include(selector)
                    .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
                    .analyze();

                  console.log(
                    `Violations for hover ${selector}: ${hoverResults.violations.length}`
                  );

                  if (hoverResults.violations.length > 0) {
                    // Add ada-violation highlight class to element
                    await el.evaluate(node => node.classList.add('ada-violation'));

                    const screenshotPath = path.resolve(
                      './test-results/screenshots',
                      `axe-hover-${selector.replace(/[^a-z0-9]/gi, '_')}-${Date.now()}.png`
                    );
                    await page.screenshot({ path: screenshotPath, fullPage: true });

                    // Remove ada-violation highlight class
                    await el.evaluate(node => node.classList.remove('ada-violation'));

                    hoverResults.violations.forEach(v => {
                      v.reportId = nextId++;
                      v.state = 'hover';
                      v.viewport = viewport.name;
                    });

                    combinedResults.push({
                      url,
                      violations: hoverResults.violations,
                      screenshot: screenshotPath,
                      viewport: viewport.name,
                      state: 'hover',
                    });

                    console.log(`Added hover result for ${selector}`);
                  }
                });
              }
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
          outputDir: reportDir,
          reportFileName: 'combined-accessibility-report.html',
          customSummary: screenshotSummary,
        },
      });

      console.log(
        `Accessibility report saved at ${path.join(reportDir, 'combined-accessibility-report.html')}`
      );

      // Inline injection of screenshots, viewport and url
      const reportPath = path.join(reportDirPath, 'combined-accessibility-report.html');
      let reportHtml = fs.readFileSync(reportPath, 'utf8');

      combinedResults.forEach((result, resultIndex) => {
        if (!result.screenshot) return;

        result.violations.forEach((violation) => {
          // Try to find the matching violation by Axe rule ID
          const ruleId = violation.id;
          const idNumber = resultIndex + 1;

          console.log(`Injecting screenshot for violation ${idNumber} (${ruleId})`);

          // Regex to match the <a id="X"> anchor and its violation card
          const regex = new RegExp(`(<a id=\"${idNumber}\">[\\s\\S]*?<\\/div>)`, 'g');

          const imgTag = `
            <p>
              <strong>Breakpoint:</strong> ${result.viewport}<br>
              <strong>URL:</strong> <a href="${result.url}" target="_blank" rel="noopener noreferrer">${result.url}</a><br>
              <strong>State:</strong> ${result.state}
            </p>

            <img src=\"${path.relative(reportDirPath, result.screenshot)}\"
              alt=\"Screenshot for violation ${idNumber}\"
              style=\"max-width: 100%; margin:1rem 0;\"
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
