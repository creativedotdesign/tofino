import { parseStringPromise } from 'xml2js';
export const processSitemap = async (sitemapUrl: string): Promise<string[]> => {
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
      const subUrls = await processSitemap(subSitemap);
      allUrls.push(...subUrls);
    }
  }

  // If URL set
  else if (parsedXml.urlset && parsedXml.urlset.url) {
    allUrls = parsedXml.urlset.url.map((entry: any) => entry.loc[0]);
  }

  return allUrls;
};
