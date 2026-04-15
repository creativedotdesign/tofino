import type { ThemeScript } from '@/js/shared/types/types';
import { loadManifestScripts, loadThemeScripts } from '@/js/core/frontendLoader';
import { frontendFeatureScripts, moduleScripts } from '@/js/core/themeAssets';
// import * as WebFont from 'webfontloader';
// import { WebFontConfig } from '@/js/shared/types/types';

// Import CSS
import '@/css/app.css';
import.meta.glob('../../features/*/style.css', { eager: true });
import.meta.glob('../../modules/*/style.css', { eager: true });

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
  await loadManifestScripts(moduleScripts, 'module');
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
