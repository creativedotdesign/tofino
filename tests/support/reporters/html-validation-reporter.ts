import type { Reporter, TestCase } from '@playwright/test/reporter';
import fs from 'fs';
import mustache from 'mustache';
import path from 'path';
import { fileURLToPath } from 'url';
import dotenv from 'dotenv';
import type { Message } from 'html-validate';

dotenv.config();

/** A message as the html spec annotates it: html-validate fields plus snippet, or a bare error message. */
type ReportMessage = Partial<Message> & { message: string; snippet?: string; ruleUrl?: string };

/**
 * Collects `html-validation` annotations from the html audit spec and renders
 * tests/output/html-validation-report.html from the mustache template.
 */
class HTMLReporter implements Reporter {
  private outputDir: string = path.resolve('./tests/output');
  public validationResults: Array<{
    testName: string;
    url: string;
    messages: ReportMessage[];
    screenshot?: string;
  }> = [];
  public timestamp: string = new Date().toLocaleString();
  private websiteName: string = process.env.VITE_ASSET_URL ?? '';

  onTestEnd(test: TestCase) {
    const validationAnnotations = test.annotations.filter(
      (annotation) => annotation.type === 'html-validation',
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
    // Nothing to report (spec not run or clean): leave no empty file behind.
    if (!this.validationResults.length) return;

    const template = this.getTemplate();

    // Check if output directory exists
    if (!fs.existsSync(this.outputDir)) {
      fs.mkdirSync(this.outputDir, { recursive: true });
    }

    // Adjust screenshot paths
    const adjustedResults = this.validationResults.map((result) => ({
      ...result,
      messages: result.messages.map((msg) => ({
        ...msg,
        ruleUrl: msg.ruleUrl || '#',
      })),
      screenshot: result.screenshot
        ? path.relative(this.outputDir, result.screenshot).replace(/\\/g, '/')
        : undefined,
    }));

    // Calculate total violations
    const totalViolations = adjustedResults.reduce((acc, curr) => acc + curr.messages.length, 0);

    const htmlContent = mustache.render(template, {
      data: adjustedResults,
      timestamp: this.timestamp,
      websiteName: this.websiteName,
      totalViolations,
    });

    fs.writeFileSync(path.join(this.outputDir, 'html-validation-report.html'), htmlContent);
  }

  getTemplate() {
    return fs.readFileSync(
      path.join(
        path.dirname(fileURLToPath(import.meta.url)),
        '../templates/html-validation-template.html',
      ),
      'utf8',
    );
  }
}

export default HTMLReporter;
