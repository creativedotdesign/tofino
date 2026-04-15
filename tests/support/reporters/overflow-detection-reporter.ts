import type { Reporter, TestCase } from '@playwright/test/reporter';
import fs from 'fs';
import mustache from 'mustache';
import path from 'path';
import dotenv from 'dotenv';

dotenv.config();

class OverflowReporter implements Reporter {
  private outputDir: string = path.resolve('./tests/output');
  private overflowResults: Array<{
    testName: string;
    url: string;
    selectors: string[];
    screenshot?: string;
  }> = [];
  private timestamp: string = new Date().toLocaleString();
  private websiteName: string = process.env.VITE_ASSET_URL ?? '';

  onTestEnd(test: TestCase) {
    const overflowAnnotations = test.annotations.filter(
      (annotation) => annotation.type === 'overflow-detection'
    );

    overflowAnnotations.forEach((annotation) => {
      const parsed = JSON.parse(annotation.description);

      this.overflowResults.push({
        testName: test.title,
        url: parsed.url,
        selectors: parsed.selectors,
        screenshot: parsed.screenshot,
      });
    });
  }

  onEnd() {
    const template = this.getTemplate();

    // Check if output directory exists
    if (!fs.existsSync(this.outputDir)) {
      fs.mkdirSync(this.outputDir, { recursive: true });
    }

    // Adjust screenshot paths
    const adjustedResults = this.overflowResults.map((res) => ({
      ...res,
      screenshot: res.screenshot
        ? path.relative(this.outputDir, res.screenshot).replace(/\\/g, '/')
        : undefined,
    }));

    // Combine all selectors
    const totalElements = adjustedResults.reduce(
      (acc, curr) => acc + curr.selectors.length,
      0
    );

    const htmlContent = mustache.render(template, {
      data: adjustedResults,
      timestamp: this.timestamp,
      websiteName: this.websiteName,
      // totalOverflowingElements,
      totalElements,
    });

    // Generate report
    fs.writeFileSync(path.join(this.outputDir, 'overflow-detection-report.html'), htmlContent);
  }

  getTemplate() {
    return fs.readFileSync('./tests/support/templates/overflow-detection-template.html', 'utf8');
  }
}

export default OverflowReporter;
