import { gunzipSync, gzipSync } from 'zlib';
import type { IncomingMessage, ServerResponse } from 'http';

type JsonValue = string | number | boolean | null | JsonValue[] | { [key: string]: JsonValue };

/**
 * Recursively replaces absolute site URLs with relative paths in a JSON structure.
 *
 * @param obj - The JSON value to process.
 * @param siteUrl - The absolute site URL to strip.
 * @returns The JSON value with URLs rewritten.
 */
const rewriteUrls = (obj: JsonValue, siteUrl: string): JsonValue => {
  if (typeof obj === 'string') {
    return obj.replaceAll(siteUrl, '');
  }

  if (Array.isArray(obj)) {
    return obj.map((item) => rewriteUrls(item, siteUrl));
  }

  if (typeof obj === 'object' && obj !== null) {
    for (const key in obj) {
      obj[key] = rewriteUrls(obj[key], siteUrl);
    }
  }

  return obj;
};

/**
 * Sends a response with updated content-length header.
 *
 * @param res - The outgoing server response.
 * @param proxyRes - The incoming proxy response (used for status code and headers).
 * @param body - The response body to send.
 */
const sendResponse = (
  res: ServerResponse,
  proxyRes: IncomingMessage,
  body: string | Buffer,
): void => {
  res.writeHead(proxyRes.statusCode!, {
    ...proxyRes.headers,
    'content-length': Buffer.byteLength(body),
  });
  res.end(body);
};

/**
 * Creates a proxy response handler that rewrites absolute WordPress URLs
 * to relative URLs in GraphQL JSON responses. Handles gzip-encoded responses.
 *
 * @param siteUrl - The absolute WordPress site URL to rewrite.
 * @returns A proxy response event handler.
 */
export const createProxyHandler =
  (siteUrl: string) =>
  (proxyRes: IncomingMessage, _req: IncomingMessage, res: ServerResponse): void => {
    const chunks: Buffer[] = [];

    proxyRes.on('data', (chunk: Buffer) => chunks.push(chunk));
    proxyRes.on('end', () => {
      const raw = Buffer.concat(chunks);
      const isGzip = proxyRes.headers['content-encoding'] === 'gzip';

      try {
        const body = isGzip ? gunzipSync(raw).toString() : raw.toString();
        const json = rewriteUrls(JSON.parse(body), siteUrl);
        const modified = JSON.stringify(json);

        sendResponse(res, proxyRes, isGzip ? gzipSync(modified) : modified);
      } catch (err) {
        console.error('Error processing proxy response:', err);
        sendResponse(res, proxyRes, raw);
      }
    });
  };
