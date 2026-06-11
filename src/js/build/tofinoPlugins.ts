import fs from 'node:fs';
import path from 'node:path';

/**
 * Discover Tofino-aligned plugins in `wp-content/plugins/` by their
 * `module.json` manifests — the same convention Tofino's PHP side uses to
 * register plugin modules via the `tofino/register_modules` filter.
 *
 * The canonical layout is `<plugin>/modules/<slug>/module.json`. A plugin
 * opts in to dev-server participation (HMR, Tailwind source scanning,
 * full-reload on PHP template changes) purely by shipping at least one
 * such manifest. Plugins without one are ignored — so a non-Tofino plugin
 * that happens to use a `modules/` directory name won't be picked up.
 *
 * @param pluginsDir Absolute path to `wp-content/plugins/`.
 * @returns Absolute paths of Tofino-aligned plugin directories.
 */
export const findTofinoPluginDirs = (pluginsDir: string): string[] => {
  if (!fs.existsSync(pluginsDir)) return [];

  return fs
    .readdirSync(pluginsDir, { withFileTypes: true })
    .filter((entry) => entry.isDirectory() && !entry.name.startsWith('.'))
    .map((entry) => path.join(pluginsDir, entry.name))
    .filter(hasTofinoManifest);
};

/**
 * Discover sibling child themes in `wp-content/themes/` by the same
 * `modules/<slug>/module.json` convention, excluding the theme itself.
 * A child theme opts in to dev-server participation (served via /@fs/,
 * full-reload on PHP changes) by shipping at least one manifest.
 *
 * @param themesDir Absolute path to `wp-content/themes/`.
 * @param selfDir   Absolute path of this theme (excluded from results).
 * @returns Absolute paths of Tofino-aligned sibling theme directories.
 */
export const findSiblingThemeDirs = (themesDir: string, selfDir: string): string[] => {
  if (!fs.existsSync(themesDir)) return [];

  return fs
    .readdirSync(themesDir, { withFileTypes: true })
    .filter((entry) => entry.isDirectory() && !entry.name.startsWith('.'))
    .map((entry) => path.join(themesDir, entry.name))
    .filter((dir) => path.resolve(dir) !== path.resolve(selfDir))
    .filter(hasTofinoManifest);
};

/**
 * Returns true when a plugin directory contains at least one
 * `modules/<slug>/module.json`.
 */
const hasTofinoManifest = (pluginDir: string): boolean => {
  const modulesDir = path.join(pluginDir, 'modules');
  if (!fs.existsSync(modulesDir)) return false;

  return fs
    .readdirSync(modulesDir, { withFileTypes: true })
    .some(
      (entry) =>
        entry.isDirectory() && fs.existsSync(path.join(modulesDir, entry.name, 'module.json')),
    );
};
