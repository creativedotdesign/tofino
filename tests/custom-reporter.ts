import type { Reporter, TestCase } from '@playwright/test/reporter';
import fs from 'fs';
import mustache from 'mustache';

class HTMLReporter implements Reporter {
  public errors: Array<{
    testTitle: string;
    errors: any[];
  }> = [];

  constructor() {
    this.validationResults = [];
  }

  onTestEnd(test: TestCase) {
    const validationAnnotations = test.annotations.filter(
      (annotation) => annotation.type === 'html-validation'
    );

    validationAnnotations.forEach((annotation) => {
      const parsed = JSON.parse(annotation.description);

      this.validationResults.push({
        testName: test.title,
        url: parsed.url,
        errorCount: parsed.results.results[0].errorCount,
        warningCount: parsed.results.results[0].warningCount,
        messages: parsed.results.results[0].messages
      });
    });
  }

  onEnd() {
    const template = this.getTemplate();

    const htmlContent = mustache.render(template, {
      data: this.validationResults
    });

    fs.writeFileSync('./test-results/custom-html-report.html', htmlContent);
  }

  getTemplate() {
    return fs.readFileSync('./tests/template/custom-template.html', 'utf8');
  }
}

export default HTMLReporter;
