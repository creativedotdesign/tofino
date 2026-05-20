import type { Plugin } from 'vite';

type InjectPluginSourcesOptions = {
  /** Absolute paths or globs injected as `@source` directives. */
  sources: string[];
  /** CSS filename suffix to target (default: `'app.css'`). */
  target?: string;
};

/**
 * Dev-only plugin that prepends `@source` directives to the theme's main CSS
 * in memory, so the theme's Tailwind scans plugin source files served through
 * this dev server via /@fs/ during development.
 *
 * The CSS file on disk is never modified. In production this plugin doesn't
 * run, so the theme's `vite build` output contains only utilities used by
 * theme source — Tofino-aligned plugins ship their own CSS from their own
 * Vite builds, and the theme's dist stays free of plugin utilities.
 *
 * Absolute paths are used so Tailwind doesn't need to resolve `../../..`
 * traversal relative to the CSS file's location.
 */
const injectPluginSources = (options: InjectPluginSourcesOptions): Plugin => {
  const target = options.target ?? 'app.css';

  return {
    name: 'tofino:inject-plugin-sources',
    apply: 'serve',
    enforce: 'pre',
    transform(code, id) {
      if (!id.endsWith(target)) return;
      if (!options.sources.length) return;

      const directives = options.sources.map((src) => `@source '${src}';`).join('\n');

      return `${directives}\n${code}`;
    },
  };
};

export default injectPluginSources;
