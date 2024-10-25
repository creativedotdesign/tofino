import zlib from 'zlib';

// Function to convert absolute URLs to relative URLs
const convertToRelativeUrl = (url) => {
  const siteUrl = process.env.VITE_LOCAL_URL;
  return url.replace(siteUrl, '');
};

// Function to recursively rewrite URLs in the JSON response
const rewriteUrls = (obj) => {
  if (typeof obj === 'string' && obj.includes(process.env.VITE_LOCAL_URL)) {
    return convertToRelativeUrl(obj);
  } else if (typeof obj === 'object' && obj !== null) {
    for (const key in obj) {
      obj[key] = rewriteUrls(obj[key]);
    }
  }
  return obj;
};

// Function to modify the response body and send it
const modifyResponseBody = (body, proxyRes, res, encoding) => {
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
const sendModifiedResponse = (res, proxyRes, body) => {
  res.writeHead(proxyRes.statusCode, {
    ...proxyRes.headers,
    'content-length': Buffer.byteLength(body),
  });
  res.end(body);
};

// Function to send the original response in case of an error
const sendOriginalResponse = (res, proxyRes, body) => {
  res.writeHead(proxyRes.statusCode, proxyRes.headers);
  res.end(body);
};

// Middleware to intercept and modify GraphQL responses
export const onProxyRes = (proxyRes, req, res) => {
  let body: Buffer[] = [];

  proxyRes.on('data', (chunk) => body.push(chunk));
  proxyRes.on('end', () => {
    body = Buffer.concat(body);

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
