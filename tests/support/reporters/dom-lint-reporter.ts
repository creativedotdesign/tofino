import type { Reporter, TestCase } from '@playwright/test/reporter';
import fs from 'fs';
import mustache from 'mustache';
import path from 'path';
import { fileURLToPath } from 'url';
import dotenv from 'dotenv';

dotenv.config();

import type { DomLintFinding } from '../utils/dom-lint';

/**
 * Collects `dom-lint` annotations from the DOM-lint audit spec and renders
 * tests/output/dom-lint-report.html: findings grouped by rule (so a sitewide
 * defect reads as one item) and then by page, with screenshots.
 */
class DomLintReporter implements Reporter {
  private outputDir: string = path.resolve('./tests/output');
  private results: Array<{ url: string; findings: DomLintFinding[]; screenshot?: string }> = [];
  private timestamp: string = new Date().toLocaleString();
  private websiteName: string = process.env.VITE_ASSET_URL ?? '';

  /** Gathers one annotation per audited page that had findings. */
  onTestEnd(test: TestCase) {
    test.annotations
      .filter((annotation) => annotation.type === 'dom-lint')
      .forEach((annotation) => {
        const parsed = JSON.parse(annotation.description ?? '{}');
        this.results.push({
          url: parsed.url,
          findings: parsed.findings,
          screenshot: parsed.screenshot,
        });
      });
  }

  /** Writes the HTML report once every test has finished. */
  onEnd() {
    if (!this.results.length) return;

    fs.mkdirSync(this.outputDir, { recursive: true });

    // Group by rule across pages so a sitewide defect reads as one item.
    const byRule = new Map<
      string,
      {
        rule: string;
        level: string;
        sc: string;
        pages: Array<{ url: string; selector: string; detail: string }>;
      }
    >();
    this.results.forEach(({ url, findings }) => {
      findings.forEach((f) => {
        const entry = byRule.get(f.rule) ?? { rule: f.rule, level: f.level, sc: f.sc, pages: [] };
        entry.pages.push({ url, selector: f.selector, detail: f.detail });
        byRule.set(f.rule, entry);
      });
    });

    const rules = [...byRule.values()]
      .map((r) => ({
        ...r,
        count: r.pages.length,
        pageCount: new Set(r.pages.map((p) => p.url)).size,
      }))
      .sort((a, b) => (a.level === b.level ? b.count - a.count : a.level === 'error' ? -1 : 1));

    const pages = this.results.map((res) => ({
      ...res,
      errorCount: res.findings.filter((f) => f.level === 'error').length,
      reviewCount: res.findings.filter((f) => f.level === 'review').length,
      screenshot: res.screenshot
        ? path.relative(this.outputDir, res.screenshot).replace(/\\/g, '/')
        : undefined,
    }));

    const html = mustache.render(this.getTemplate(), {
      rules,
      pages,
      timestamp: this.timestamp,
      websiteName: this.websiteName,
      totalErrors: pages.reduce((n, p) => n + p.errorCount, 0),
      totalReview: pages.reduce((n, p) => n + p.reviewCount, 0),
    });

    fs.writeFileSync(path.join(this.outputDir, 'dom-lint-report.html'), html);
  }

  /** Mustache template for the report. */
  getTemplate(): string {
    return fs.readFileSync(
      path.join(
        path.dirname(fileURLToPath(import.meta.url)),
        '../templates/dom-lint-template.html',
      ),
      'utf8',
    );
  }
}

export default DomLintReporter;
