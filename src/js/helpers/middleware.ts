import zlib from 'zlib';
import { IncomingMessage, ServerResponse } from 'http';

// Function to convert absolute URLs to relative URLs
const convertToRelativeUrl = (url: string): string => {
  const siteUrl = process.env.VITE_LOCAL_URL;
  if (!siteUrl) {
    throw new Error('VITE_LOCAL_URL is not defined');
  }
  return url.replace(siteUrl, '');
};

// Function to recursively rewrite URLs in the JSON response
const rewriteUrls = (obj: any): any => {
  if (typeof obj === 'string' && obj.includes(process.env.VITE_LOCAL_URL!)) {
    return convertToRelativeUrl(obj);
  } else if (typeof obj === 'object' && obj !== null) {
    for (const key in obj) {
      if (Object.prototype.hasOwnProperty.call(obj, key)) {
        obj[key] = rewriteUrls(obj[key]);
      }
    }
  }
  return obj;
};

// Function to modify the response body and send it
const modifyResponseBody = async (
  body: string,
  proxyRes: IncomingMessage,
  res: ServerResponse,
  encoding: string | undefined
) => {
  try {
    let json = JSON.parse(body);
    json = rewriteUrls(json);
    const modifiedBody = JSON.stringify(json);

    if (encoding === 'gzip') {
      zlib.gzip(modifiedBody, (err, compressed) => {
        if (err) {
          console.error('Error compressing response:', err);
          sendOriginalResponse(res, proxyRes, body);
          return;
        }
        sendModifiedResponse(res, proxyRes, compressed);
      });
    } else {
      sendModifiedResponse(res, proxyRes, modifiedBody);
    }
  } catch (err) {
    console.error('Error parsing JSON response:', err);
    sendOriginalResponse(res, proxyRes, body);
  }
};

// Function to send the modified response
const sendModifiedResponse = (
  res: ServerResponse,
  proxyRes: IncomingMessage,
  body: string | Buffer
) => {
  res.writeHead(proxyRes.statusCode!, {
    ...proxyRes.headers,
    'content-length': Buffer.byteLength(body),
  });
  res.end(body);
};

// Function to send the original response in case of an error
const sendOriginalResponse = (
  res: ServerResponse,
  proxyRes: IncomingMessage,
  body: string | Buffer
) => {
  res.writeHead(proxyRes.statusCode!, proxyRes.headers);
  res.end(body);
};

// Middleware to intercept and modify GraphQL responses
export const onProxyRes = (
  proxyRes: IncomingMessage,
  req: IncomingMessage,
  res: ServerResponse
) => {
  let bodyChunks: Buffer[] = [];

  proxyRes.on('data', (chunk) => bodyChunks.push(chunk));
  proxyRes.on('end', () => {
    const body = Buffer.concat(bodyChunks);

    const encoding = proxyRes.headers['content-encoding'];
    if (encoding === 'gzip') {
      zlib.gunzip(body, (err, decoded) => {
        if (err) {
          console.error('Error decompressing response:', err);
          sendOriginalResponse(res, proxyRes, body);
          return;
        }
        modifyResponseBody(decoded.toString(), proxyRes, res, encoding);
      });
    } else {
      modifyResponseBody(body.toString(), proxyRes, res, encoding);
    }
  });
};

export default onProxyRes;
