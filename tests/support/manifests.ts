import fs from 'fs';
import path from 'path';

/**
 * Accessibility contracts declared by modules and features in their manifests.
 *
 * A module or feature that owns interactive UI describes it under an `a11y`
 * key in its `module.json` / `feature.json`, next to the template and script
 * it already declares. The generic specs in the parent theme read those
 * declarations from whatever theme the suite is run from, so theme knowledge
 * stays with the theme:
 *
 *   "a11y": {
 *     "overlays": [{ "trigger": "[data-header-open]", "container": "#header-menu", "close": "[data-header-close]" }],
 *     "carousel": { "prev": "[data-km-prev]", "next": "[data-km-next]" },
 *     "dismiss": [".js-popup-close"],
 *     "exclude": ["[data-embed-target]"]
 *   }
 *
 * Native <dialog> openers (`button[aria-haspopup="dialog"][aria-controls]`)
 * and Swiper's own navigation classes are discovered without a declaration.
 */

/** A modal overlay: what opens it, what it is, and (optionally) what closes it. */
export type OverlayContract = {
  /** Shown in test output. Defaults to the manifest title. */
  name?: string;
  /** Selector for the control that opens the overlay. */
  trigger: string;
  /** Selector for the overlay container; `[open]`-style selectors work for repeated dialogs. */
  container: string;
  /** Selector for an explicit close control inside the container. */
  close?: string;
};

/** Selectors for a carousel's previous / next controls. */
export type CarouselContract = {
  prev?: string;
  next?: string;
};

/** The `a11y` key of a module or feature manifest. */
export type A11yContract = {
  overlays?: OverlayContract[];
  carousel?: CarouselContract;
  /** Controls to click before behavioural specs (cookie banners, popups). */
  dismiss?: string[];
  /**
   * Containers whose CONTENTS are third-party markup the site cannot change
   * (Tableau, ZoomProspector, LocalIntel, video players). Everything inside
   * is left out of the audits; the container element itself (an <iframe>'s
   * title, say) is still checked.
   */
  exclude?: string[];
};

/** Site-level test settings from the theme's `tests/a11y.config.json`. */
export type SiteConfig = {
  /** Representative pages to audit instead of crawling the sitemap. */
  urls: string[];
  /** Site-level exclusions, same meaning as a manifest `exclude`. */
  exclude: string[];
};

/**
 * Reads `tests/a11y.config.json` from `root`. Absent → empty lists (the
 * sitemap is crawled); invalid JSON throws, naming the file.
 */
export const readSiteConfig = (root: string = process.cwd()): SiteConfig => {
  const file = path.join(root, 'tests/a11y.config.json');
  if (!fs.existsSync(file)) return { urls: [], exclude: [] };
  try {
    const parsed = JSON.parse(fs.readFileSync(file, 'utf8')) as Partial<SiteConfig>;
    return {
      urls: Array.isArray(parsed.urls) ? parsed.urls : [],
      exclude: Array.isArray(parsed.exclude) ? parsed.exclude : [],
    };
  } catch (error) {
    throw new Error(
      `tests/a11y.config.json: invalid JSON (${error instanceof Error ? error.message : error})`,
    );
  }
};

/** A contract with the manifest it came from, for messages. */
export type ManifestContract = A11yContract & { source: string; title: string };

/**
 * Reads every `modules/＊/module.json` and `features/＊/feature.json` under
 * `root`, plus `modules/＊/module.json` in each sibling plugin
 * (`<wp-content>/plugins/＊/`, the modules plugins register through the
 * `tofino/register_modules` filter), and returns the `a11y` contracts they
 * declare. A manifest that is not valid JSON throws, naming the file, so a
 * typo fails loudly instead of silently dropping coverage.
 *
 * @param root Theme directory to scan. Defaults to the working directory, so
 *             running the suite from a child theme picks up that theme's manifests.
 */
export const readManifestContracts = (root: string = process.cwd()): ManifestContract[] => {
  const contracts: ManifestContract[] = [];

  const scan = (folder: string, file: string, from: string = root): void => {
    const base = path.join(from, folder);
    if (!fs.existsSync(base)) return;

    for (const entry of fs.readdirSync(base, { withFileTypes: true })) {
      if (!entry.isDirectory()) continue;
      const manifestPath = path.join(base, entry.name, file);
      if (!fs.existsSync(manifestPath)) continue;

      const source = `${path.relative(root, base)}/${entry.name}/${file}`;
      let manifest: { title?: string; a11y?: A11yContract };
      try {
        manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
      } catch (error) {
        throw new Error(
          `${source}: invalid JSON (${error instanceof Error ? error.message : error})`,
        );
      }

      if (manifest.a11y) {
        contracts.push({ ...manifest.a11y, source, title: manifest.title ?? entry.name });
      }
    }
  };

  scan('modules', 'module.json');
  scan('features', 'feature.json');

  // Plugin modules: wp-content/plugins/<plugin>/modules/<module>/module.json.
  const plugins = path.resolve(root, '../../plugins');
  if (fs.existsSync(plugins)) {
    for (const plugin of fs.readdirSync(plugins, { withFileTypes: true })) {
      if (!plugin.isDirectory()) continue;
      scan('modules', 'module.json', path.join(plugins, plugin.name));
    }
  }

  return contracts;
};
