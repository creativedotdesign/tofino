import type { ThemeScript } from '@/js/shared/types/types';
import { loadManifestScripts, loadThemeScripts } from '@/js/core/frontendLoader';
import { frontendFeatureScripts, moduleScripts } from '@/js/core/themeAssets';

// Inline data from localize_scripts() — a global `const`, not a window prop.
declare const tofinoJS: { overriddenModules?: string[] } | undefined;
// import * as WebFont from 'webfontloader';
// import { WebFontConfig } from '@/js/shared/types/types';

// Front-end CSS lives in its own entry (js/styles.ts) so it can be enqueued or
// suppressed independently of this JS — see Vite::use_vite() and the
// tofino/use_vite_css filter. Don't import stylesheets here.

/**
 * Runs on DOMContentLoaded and boots front-end scripts.
 *
 * @returns void
 */
const init = async (): Promise<void> => {
  // JavaScript to be fired on all pages

  // Config for WebFontLoader
  // const fontConfig: WebFontConfig = {
  //   classes: false,
  //   events: false,
  //   google: {
  //     families: ['Roboto:300,400,500,700'],
  //     display: 'swap',
  //     version: 1.0,
  //   },
  // };

  // // Load Fonts
  // WebFont.load(fontConfig);

  const scripts: ThemeScript[] = [
    {
      selector: '#main-menu',
      src: 'menu',
    },
    {
      selector: '[data-scroll-reveal]',
      src: 'menuScrollReveal',
    },
  ];

  await loadThemeScripts(scripts);
  await loadManifestScripts(frontendFeatureScripts, 'feature');

  // Modules overridden by a child theme/plugin ship their own script — skip
  // this theme's copy so handlers aren't double-bound.
  const overridden = typeof tofinoJS !== 'undefined' ? (tofinoJS?.overriddenModules ?? []) : [];
  await loadManifestScripts(moduleScripts, 'module', overridden);
};

/**
 * Runs on the window `load` event, after all resources (images, stylesheets,
 * sub-frames) have finished loading.
 *
 * @returns void
 */
const loaded = () => {
  // Javascript to be fired once fully loaded
};

// DOM Ready
window.addEventListener('DOMContentLoaded', () => {
  void init();
});

// Fully loaded
window.addEventListener('load', () => {
  loaded();
});
