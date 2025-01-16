import { test, expect } from '@playwright/test';
import { HtmlValidate } from 'html-validate';

test('Validate HTML', async ({ page, baseURL }) => {
  await page.goto(baseURL);

  // Get the HTML content
  const html = await page.content();

  // Create an html-validate instance
  const htmlvalidate = new HtmlValidate();

  // Validate the HTML
  const report = await htmlvalidate.validateString(html);

  // Attach the results to the test metadata for the reporter
  if (!report.valid) {
    test.info().annotations.push({
      type: 'html-validation',
      description: JSON.stringify({
        url: baseURL,
        results: report,
      }),
    });
  }

  // console.log('report:', report.results);

  // if (report.results.length > 0) {
  //   for (const item of report.results) {
  //     console.log(item.messages);
  //   }
  // }

  // if (!report.valid) {
  //   await test.info().attach('html-validate-report.json', {
  //     contentType: 'application/json',
  //     body: Buffer.from(JSON.stringify(report.results, null, 2)),
  //   });
  // }

  expect(report.valid, 'HTML did not pass validation').toBeTruthy();
});
