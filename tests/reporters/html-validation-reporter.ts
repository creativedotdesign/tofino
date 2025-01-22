import type { Reporter, TestCase } from '@playwright/test/reporter';
import fs from 'fs';
import mustache from 'mustache';
import path from 'path';
import dotenv from 'dotenv';

dotenv.config();

class HTMLReporter implements Reporter {
  public validationResults: Array<{
    testName: string;
    url: string;
    messages: any[];
    screenshot?: string;
  }> = [];
  public timestamp: string = new Date().toLocaleString();
  private websiteName: string = process.env.VITE_ASSET_URL ?? '';

  onTestEnd(test: TestCase) {
    const validationAnnotations = test.annotations.filter(
      (annotation) => annotation.type === 'html-validation'
    );

    validationAnnotations.forEach((annotation) => {
      const parsed = JSON.parse(annotation.description);

      this.validationResults.push({
        testName: test.title,
        url: parsed.url,
        messages: parsed.results.results[0].messages,
        screenshot: parsed.screenshot,
      });
    });
  }

  onEnd() {
    const template = this.getTemplate();

    // Check if output directory exists
    const outputDir = path.resolve('./test-results');
    if (!fs.existsSync(outputDir)) {
      fs.mkdirSync(outputDir, { recursive: true });
    }

    // Adjust screenshot paths
    const adjustedResults = this.validationResults.map((result) => ({
      ...result,
      messages: result.messages.map((msg: any) => ({
        ...msg,
        ruleUrl: msg.ruleUrl || '#',
      })),
      screenshot: result.screenshot
        ? path.relative(outputDir, result.screenshot).replace(/\\/g, '/')
        : undefined,
    }));

    // Calculate total violations
    const totalViolations = adjustedResults.reduce(
      (acc, curr) => acc + curr.messages.length,
      0
    );

    const htmlContent = mustache.render(template, {
      data: adjustedResults,
      timestamp: this.timestamp,
      websiteName: this.websiteName,
      totalViolations,
    });

    fs.writeFileSync('./test-results/html-validation-report.html', htmlContent);
  }

  getTemplate() {
    return fs.readFileSync('./tests/templates/html-validation-template.html', 'utf8');
  }
}

export default HTMLReporter;
