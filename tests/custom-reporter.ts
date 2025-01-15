import type {
  FullConfig, FullResult, Reporter, Suite, TestCase, TestResult
} from '@playwright/test/reporter';

import fs from 'fs';
import mustache from 'mustache';
// import path from 'path';

class HTMLReporter implements Reporter {
  // private errors: any[] = [];

  constructor() {
    console.log(`Using Custom reporter`);
  }

  // onBegin(_: FullConfig, suite: Suite) {
  // }

  public errors = [];

  onTestBegin(test: TestCase, result: TestResult) {
    console.log(`Starting test ${test.title}`);
  }

  onTestEnd(test: TestCase, result: TestResult) {
    console.log(`Finished test ${test.title}: ${result.status}`);
  }

  onEnd(result: FullResult) {
    console.log('End of all tests!');
    console.log('Final status:', result.status);

    // Load the template
    const template = this.getTemplate();

    const mustacheData = {
      errors: this.errors,
    };

    const htmlContent = mustache.render(template, mustacheData);

    fs.writeFileSync('./test-results/custom-html-report.html', htmlContent);

    // console.log(this.errors.toString());
    // console.log(template);
    // const htmlContent = template.replace('{{ errors }}', 'My replaced content!');

    // Process results into HTML File.


  }

  onStdOut(chunk: string | Buffer, _: void | TestCase, __: void | TestResult): void {
    const text = chunk.toString("utf-8");

    let error = text.replace(/[[\]]/g, '');

    const errors = [
      {
        ruleId: 'attribute-boolean-style',
        severity: 2,
        message: 'Attribute "defer" should omit value',
        offset: 2155,
        line: 23,
        column: 9,
        size: 5,
        selector: '#wpml-cookie-js',
        ruleUrl: 'https://html-validate.org/rules/attribute-boolean-style.html'
      },
      {
        ruleId: 'no-implicit-button-type',
        severity: 2,
        message: '<button> is missing recommended "type" attribute',
        offset: 20140,
        line: 1075,
        column: 8,
        size: 6,
        selector: 'html > body > header > nav > button',
        ruleUrl: 'https://html-validate.org/rules/no-implicit-button-type.html'
      },
    ];

    this.errors.push(errors);

    process.stdout.write(text);
  }

  onStdErr(chunk: string | Buffer, _: TestCase, __: TestResult) {
    const text = chunk.toString("utf-8");
    process.stderr.write(text);
  }

  getTemplate() {
    return fs.readFileSync('./tests/template/custom-template.html', 'utf8');
  }
}

export default HTMLReporter;
