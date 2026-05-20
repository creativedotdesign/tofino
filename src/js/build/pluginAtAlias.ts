import type { Plugin } from 'vite';
import path from 'node:path';

type PluginAtAliasOptions = {
  /** Absolute path used to resolve `@/...` for theme importers (typically `theme/src`). */
  themeSrcDir: string;
};

/**
 * Resolves `@/...` imports for both theme code and Tofino-aligned plugin code:
 *
 * - Theme importer (anywhere under the theme dir) → `themeSrcDir/...`
 * - Plugin importer (`wp-content/plugins/<name>/modules/<slug>/...`) →
 *   `<that module dir>/...` (same semantics each plugin's own `vite.config.ts`
 *   declares via its `@` alias for its prod build)
 *
 * This plugin replaces the theme's `'@': './src'` entry in `resolve.alias`.
 * That had to move because Vite's built-in alias plugin runs *before* user
 * plugins with `enforce: 'pre'`, so a static `@` alias would intercept
 * plugin imports and resolve them to the wrong directory before this plugin
 * could see them.
 *
 * Preserves Vite query suffixes (`?raw`, `?url`, `?component`, etc.) and
 * delegates back to Vite via `this.resolve` so extension / index resolution
 * still happens.
 */
const pluginAtAlias = ({ themeSrcDir }: PluginAtAliasOptions): Plugin => ({
  name: 'tofino:plugin-at-alias',
  enforce: 'pre',
  async resolveId(source, importer, options) {
    if (!source.startsWith('@/') || !importer) return null;

    const [importerPath] = importer.split('?', 1);
    const normalized = importerPath.replace(/^\/@fs\//, '/');

    let baseDir: string | null = null;

    const pluginMatch = normalized.match(/^(.*\/wp-content\/plugins\/[^/]+\/modules\/[^/]+)\//);
    if (pluginMatch) {
      baseDir = pluginMatch[1];
    } else if (normalized.includes('/themes/')) {
      baseDir = themeSrcDir;
    }

    if (!baseDir) return null;

    const sourcePath = source.slice(2);
    const [src, query] = sourcePath.split('?', 2);
    const resolvedPath = path.resolve(baseDir, src);
    const target = query ? `${resolvedPath}?${query}` : resolvedPath;

    // Delegate back to Vite for extension / index resolution.
    return this.resolve(target, importer, { ...options, skipSelf: true });
  },
});

export default pluginAtAlias;
