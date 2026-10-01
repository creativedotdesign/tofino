import fs from 'fs';
import path from 'path';
import { crawl, extraUrls, maxUrls, site } from '../config';
import { processSitemap } from './process-sitemap';

/**
 * Pages every WordPress site has that never appear in a sitemap. The search
 * results template is the only query-string page common to all sites; add
 * others per run with A11Y_URLS.
 */
const defaultExtras = ['/?s=lorem'];

/**
 * Builds the URL list for an audit: the site's curated list (or the sitemap
 * crawl), the default extras, plus any URLs from A11Y_URLS, capped by
 * A11Y_MAX_URLS. The homepage is always first so a capped run still covers
 * the richest template. The list is written to tests/output/urls.json.
 */
export const getAuditUrls = async (
  baseURL: string | undefined,
): Promise<{ urls: string[]; counts: Record<string, number> }> => {
  if (!baseURL) {
    throw new Error('baseURL is not set. Add VITE_ASSET_URL to .env.');
  }

  const base = baseURL.replace(/\/$/, '');
  // Curated pages from tests/a11y.config.json (see tests/support/manifests.ts);
  // sites repeat templates across hundreds of URLs, so a short list covers
  // the markup in minutes. Absent, or with A11Y_CRAWL=1, crawl the sitemap.
  const siteUrls = site.urls;
  const useCrawl = crawl || siteUrls.length === 0;
  const { urls: sitemapUrls, counts } = useCrawl
    ? await processSitemap(`${base}/sitemap_index.xml`)
    : {
        urls: siteUrls.map((url) => `${base}${url}`),
        counts: { 'tests/a11y.config.json': siteUrls.length },
      };
  const extras = [...defaultExtras, ...extraUrls].map((url) =>
    url.startsWith('http') ? url : `${base}${url}`,
  );

  const home = `${base}/`;
  const ordered = [home, ...sitemapUrls.filter((url) => url !== home), ...extras];
  const unique = [...new Set(ordered)];

  const urls = unique.slice(0, maxUrls);

  // Record what this run actually audited, next to the reports.
  const outputDir = path.resolve('./tests/output');
  fs.mkdirSync(outputDir, { recursive: true });
  fs.writeFileSync(
    path.join(outputDir, 'urls.json'),
    JSON.stringify(
      {
        baseURL: base,
        source: useCrawl ? 'sitemap' : 'tests/a11y.config.json',
        count: urls.length,
        skipped: unique.length - urls.length,
        urls,
      },
      null,
      2,
    ),
  );

  return { urls, counts };
};
