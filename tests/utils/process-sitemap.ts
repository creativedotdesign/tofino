import { parseStringPromise } from 'xml2js';

export const processSitemap = async (
  sitemapUrl: string,
  counts: Record<string, number> = {}
): Promise<{ urls: string[]; counts: Record<string, number> }> => {

  // Get the sitemap content
  const response = await fetch(sitemapUrl);

  if (!response.ok) {
    throw new Error(`Failed to fetch sitemap: ${response.status}`);
  }

  // Read the text content of the sitemap
  const xmlContent = await response.text();

  // Parse the XML content to extract URLs
  const parsedXml = await parseStringPromise(xmlContent);

  let allUrls: string[] = [];

  // If Sitemap index
  if (parsedXml.sitemapindex && parsedXml.sitemapindex.sitemap) {
    const subSitemaps = parsedXml.sitemapindex.sitemap.map((entry: any) => entry.loc[0]);
    for (const subSitemap of subSitemaps) {
      const { urls, counts: _ } = await processSitemap(subSitemap, counts);
      allUrls.push(...urls);
    }
  }

  // If URL set
  else if (parsedXml.urlset && parsedXml.urlset.url) {
    allUrls = parsedXml.urlset.url.map((entry: any) => entry.loc[0]);
    counts[sitemapUrl] = allUrls.length;
  }

  return { urls: allUrls, counts };
};
