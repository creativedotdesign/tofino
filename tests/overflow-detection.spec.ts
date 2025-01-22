import { test, expect } from '@playwright/test';
import { parseStringPromise } from 'xml2js';
import path from 'path';
import fs from 'fs';
// import checkElement from './utils/overflow-check';

// console.log(checkElement);

test.describe('Overflow Detection Tests', () => {
  let urls: string[] = [];

  const overflowIssues: Array<{
    url: string;
    // overflowingCount: number;
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
        await page.goto(url, { waitUntil: 'networkidle' });

        console.log(url);

        // const overflowingCount = await page.evaluate(() => {
        //   const elements = Array.from(document.querySelectorAll<HTMLElement>('*'));
        //   let count = 0;

        //   // Check scroll against client widths and heights
        //   elements.forEach((el) => {
        //     if (el.scrollWidth > el.clientWidth || el.scrollHeight > el.clientHeight) {
        //       count++;
        //     }
        //   });
        //   return count;
        // });

        const result = await page.evaluate(() => {
          const issues: string[] = [];

          const selectors = '*';


          const warn = (el: HTMLElement) => {
            // Style the detected issues
            el.style.outline = '2px solid #FF0000';
            // el.style.backgroundColor = '#FFCC00';
            // el.style.backgroundImage = 'linear-gradient(135deg, rgba(255,0,0,1) 0%, rgba(255,204,0,1) 35%, rgba(0,212,255,1) 100%)';
          }

          const checkScrollingAncestor = (elem: HTMLElement): boolean => {
            if (!elem.parentElement || elem.parentElement.tagName.toLowerCase() === 'body') {
              return false;
            }

            const computedStyle = window.getComputedStyle(elem.parentElement);

            if (computedStyle.overflowX == 'auto') {
              return true;
            } else {
              return checkScrollingAncestor(elem.parentElement);
            }
          }

          const getSizedAncestor = (elem: HTMLElement): HTMLElement | null => {
            if (!elem.parentElement) {
              return null;
            }

            if (elem.parentElement.offsetWidth > 0) {
              return elem.parentElement;
            } else {
              return getSizedAncestor(elem.parentElement);
            }
          }

          const getSelector = (el: HTMLElement | null): string => {
            if (el == null) {
              return '';
            }

            if (el.tagName.toLowerCase() == 'html') {
              return 'HTML';
            }

            let selector = el.tagName;
            selector += (el.id) ? '#' + el.id : '';

            if (el.className) {
              const classes = el.className.split(/\s/);
              for (let i = 0; i < classes.length; i++) {
                selector += '.' + classes[i]
              }
            }
            return getSelector(el.parentElement) + ' > ' + selector;
          }

          const checkElement = (el) => {
            const hasScrollingAncestor = checkScrollingAncestor(el);
            if (hasScrollingAncestor) {
              return;
            }

            const isHidden = (el.offsetParent === null);

            if (isHidden) {
              return;
            }

            // Find elements that overflow the document width
            if (el.scrollWidth > document.documentElement.offsetWidth) {

              // console.log('Checking....', el);
              // console.log('Document width:', document.documentElement.offsetWidth);
              // console.log('Offset Element Width: ', el.scrollWidth);
              // console.log(el.attributes);

              warn(el);

              console.log('Got issues', getSelector(el));

              // return getSelector(el);

              // issues.push(el);
              issues.push(getSelector(el));
            }

            const ancestor = getSizedAncestor(el);
            const info = window.getComputedStyle(el);

            // Find any negative margins (deliberate outflow)
            const adjustment =
              (info.marginLeft.startsWith('-') ? parseFloat(info.marginLeft) * -1 : 0)
              +
              (info.marginRight.startsWith('-') ? parseFloat(info.marginRight) * -1 : 0);

            if (ancestor && (el.scrollWidth - adjustment) > ancestor.scrollWidth) {
              warn(el);
              // issues.push(el);

              console.log('Got issues', getSelector(el));

              // return getSelector(el);

              issues.push(getSelector(el));
            }
          }

          // document.querySelectorAll(selectors).forEach(checkElement);
          document.querySelectorAll(selectors).forEach((elm) => {

            // checkElement();

            const result = checkElement(elm);

            // if (result) {
            //   issues.push(result);
            // }
          });

          console.log('outside of loop', issues);

          // if (issues.length > 0) {
          //   const firstEl = document.querySelector(issues[0].split(' > ').pop()!);
          //   if (firstEl instanceof HTMLElement) {
          //     firstEl.scrollIntoView({ block: 'center', inline: 'center' });
          //   }
          // }


          return issues;
        });

        // if (overflowingCount > 0) {
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
              // overflowingCount,
              selectors: result,
              screenshot: screenshotPath,
            }),
          });

          // Collect errors for reporting
          overflowIssues.push({
            url,
            // overflowingCount,
            selectors: result,
            screenshot: screenshotPath,
          });

          // console.log(`Detected ${overflowingCount} overflowing elements on: ${url}`);
        } else {
          console.log(`No overflow issues on: ${url}`);
        }
      } catch (err: any) {
        console.error(`Error detecting overflow on ${url}:`, err);
      }
    }

    expect(overflowIssues.length, 'Some pages have overflowing elements').toBe(0);
  });
});
