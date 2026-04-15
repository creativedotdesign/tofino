import type { Plugin, ResolvedConfig } from 'vite';

type DevAssetRewriterOptions = {
  publicUrl?: string;
};

/**
 * Vite plugin that rewrites relative asset URLs in CSS to point
 * to the dev server, preventing 404s when proxying through WordPress.
 */
const devAssetRewriter = (options: DevAssetRewriterOptions = {}): Plugin => {
  let devServerUrl: string;

  return {
    name: 'vite-plugin-dev-asset-rewriter',
    apply: 'serve',
    enforce: 'pre',
    /**
     * Captures the resolved Vite configuration to build the dev server base URL.
     *
     * @param config - The fully resolved Vite configuration object.
     * @returns void
     */
    configResolved(config: ResolvedConfig) {
      const explicitPublicUrl = options.publicUrl?.trim().replace(/\/+$/, '');
      if (explicitPublicUrl) {
        devServerUrl = explicitPublicUrl;
        return;
      }

      const { https, port } = config.server;
      const protocol = https ? 'https' : 'http';
      devServerUrl = `${protocol}://localhost:${port}`;
    },
    /**
     * Rewrites relative asset URLs in CSS files to point to the dev server.
     *
     * @param code - The source code of the file being transformed.
     * @param id - The file path/identifier of the module being transformed.
     * @returns An object with updated code, or null if no changes were made.
     */
    transform(code: string, id: string) {
      if (!id.endsWith('.css')) return null;

      const updatedCode = code.replace(
        /url\((['"]?)(\/[^'")]+)\1\)/g,
        (_, quote, assetPath) => `url(${quote}${devServerUrl}${assetPath}${quote})`,
      );

      return updatedCode === code ? null : { code: updatedCode, map: null };
    },
  };
};

export default devAssetRewriter;
