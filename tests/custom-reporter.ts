import type {
  FullConfig, FullResult, Reporter, Suite, TestCase, TestResult
} from '@playwright/test/reporter';

import fs from 'fs';
import mustache from 'mustache';

class HTMLReporter implements Reporter {
  public errors: Array<{
    testTitle: string;
    errors: any[];
  }> = [];

  constructor(options) {
    this.validationResults = [];

    console.log(`Using Custom reporter`);
  }

  // onBegin(_: FullConfig, suite: Suite) {
  // }

  onTestBegin(test: TestCase, result: TestResult) {
    console.log(`Starting test ${test.title}`);
  }

  onTestEnd(test: TestCase, result: TestResult) {
    console.log(`Finished test ${test.title}: ${result.status}`);

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

    // console.log('[DEBUG] Annotations:', this.validationResults);

    for (const attachment of result.attachments) {
      // Check for attachment "html-validate-report.json"
      if (attachment.name === 'html-validate-report.json') {
        // let reportResults: any[] = [];

        // if (attachment.body) {
        // reportResults = JSON.parse(attachment.body.toString('utf-8'));
        let parsed = JSON.parse(attachment.body.toString('utf-8'));
        // Flatten Array
        const allMessages = parsed.flatMap(result => result.messages || []);
        // }

        this.errors.push({
          testTitle: test.title,
          errors: allMessages,
        });
      }
    }
  }

  onEnd(result: FullResult) {
    console.log('End of all tests!');
    console.log('Final status:', result.status);



    // console.log(this.validationResults);

    // Load the template
    const template = this.getTemplate();

    // const mustacheData = {
    //   errors: this.errors,
    // };

    // const mustacheData = this.validationResults;

    const htmlContent = mustache.render(template, {
      data: this.validationResults
    });

    fs.writeFileSync('./test-results/custom-html-report.html', htmlContent);

    // console.log(this.errors.toString());
    // console.log(template);
    // const htmlContent = template.replace('{{ errors }}', 'My replaced content!');

    // Process results into HTML File.
  }

  // onStdOut(chunk: string | Buffer, _: void | TestCase, __: void | TestResult): void {
  //   const text = chunk.toString("utf-8");

  //   let error = text.replace(/[[\]]/g, '');

  //   const errors = [
  //     {
  //       ruleId: 'attribute-boolean-style',
  //       severity: 2,
  //       message: 'Attribute "defer" should omit value',
  //       offset: 2155,
  //       line: 23,
  //       column: 9,
  //       size: 5,
  //       selector: '#wpml-cookie-js',
  //       ruleUrl: 'https://html-validate.org/rules/attribute-boolean-style.html'
  //     },
  //     {
  //       ruleId: 'no-implicit-button-type',
  //       severity: 2,
  //       message: '<button> is missing recommended "type" attribute',
  //       offset: 20140,
  //       line: 1075,
  //       column: 8,
  //       size: 6,
  //       selector: 'html > body > header > nav > button',
  //       ruleUrl: 'https://html-validate.org/rules/no-implicit-button-type.html'
  //     },
  //   ];

  //   this.errors.push(errors);

  //   process.stdout.write(text);
  // }

  onStdErr(chunk: string | Buffer, _: TestCase, __: TestResult) {
    const text = chunk.toString("utf-8");
    process.stderr.write(text);
  }

  getTemplate() {
    return fs.readFileSync('./tests/template/custom-template.html', 'utf8');
  }
}

export default HTMLReporter;
