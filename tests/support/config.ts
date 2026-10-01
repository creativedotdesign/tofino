import { readManifestContracts, readSiteConfig, type OverlayContract } from './manifests';

/**
 * Shared settings for the audit, feature and module specs.
 *
 * Everything here is site-agnostic. Theme-specific selectors come from the
 * `a11y` key of module and feature manifests in the theme the suite is run
 * from (see tests/support/manifests.ts). The URL list comes from the theme's
 * tests/a11y.config.json (see tests/support/utils/urls.ts); environment
 * variables scope a run:
 *
 *   A11Y_MAX_URLS=10              cap the URL list
 *   A11Y_URLS=/jobs/?q=x,/news/   extra URLs for this run, comma separated
 */

/** Manifest-declared contracts of the theme in the working directory. */
const contracts = readManifestContracts();

/** Site-level settings (curated URLs, exclusions) of the theme in the working directory. */
export const site = readSiteConfig();

/**
 * Third-party regions left out of every audit: manifest `a11y.exclude`
 * (e.g. the data-embed module's `[data-embed-target]`) plus the site config's
 * `exclude`. Their contents belong to the provider; the container element is
 * still checked. axe also never injects into frames (`iframes: false`), so
 * cross-origin embed content is out regardless.
 */
export const excludes: string[] = [
  ...contracts.flatMap((contract) => contract.exclude ?? []),
  ...site.exclude,
];

export const maxUrls = Number.parseInt(process.env.A11Y_MAX_URLS ?? '', 10) || Infinity;

/**
 * Run flags. All off by default so the routine run is the fast, focused one:
 *
 *   A11Y_CRAWL=1        crawl the whole sitemap instead of tests/a11y.config.json
 *   A11Y_SCREENSHOTS=1  write a full-page screenshot per page with findings
 *                       (the reports already carry selectors and snippets)
 *   A11Y_HOVER=1        hover every distinct link/button at desktop and run
 *                       axe on it (slow; audit-ada-mcp --states is the
 *                       better tool for hover and focus contrast)
 */
export const crawl = process.env.A11Y_CRAWL === '1';
export const screenshots = process.env.A11Y_SCREENSHOTS === '1';
export const hoverPass = process.env.A11Y_HOVER === '1';

export const extraUrls = (process.env.A11Y_URLS ?? '')
  .split(',')
  .map((url) => url.trim())
  .filter(Boolean);

/**
 * Controls clicked before behavioural specs (focus, overlays, carousels) so a
 * cookie banner or popup does not cover the page. The axe, html and DOM-lint
 * audits leave them in place: banners must be accessible too. The Tofino
 * cookie notice plugin is part of every Tofino site; anything else comes
 * from a manifest `a11y.dismiss`.
 */
export const dismiss: string[] = [
  '.tofino-reject-cookies',
  ...contracts.flatMap((contract) => contract.dismiss ?? []),
];

/** axe rule tags. WCAG 2.2 AA plus axe best practices (heading order, landmark labels, list semantics). */
export const axeTags = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa', 'best-practice'];

/** Viewports for the sitemap audits. 320 is the WCAG 1.4.10 reflow width. */
export const viewports = [
  { name: 'reflow-320', width: 320, height: 640 },
  { name: 'tablet', width: 768, height: 1024 },
  { name: 'desktop', width: 1280, height: 720 },
];

/** An overlay the overlays spec exercises. */
export type Overlay = OverlayContract & { name: string };

/**
 * Overlays that must behave as modal dialogs: focus moves in, is contained,
 * Escape closes, focus returns to the trigger. The parent theme's own mobile
 * menu (header.php) is listed here; child themes declare theirs in feature
 * manifests. Native <dialog> openers are discovered by the spec itself.
 */
export const overlays: Overlay[] = [
  {
    name: 'Tofino mobile menu',
    trigger: '[data-menu-open="main-menu"]',
    container: '#main-menu',
    close: '[data-menu-close="main-menu"]',
  },
  ...contracts.flatMap((contract) =>
    (contract.overlays ?? []).map((overlay) => ({
      ...overlay,
      name: overlay.name ?? `${contract.title} overlay`,
    })),
  ),
];

/**
 * Carousel roots and the controls that drive them. Swiper's own navigation
 * classes and "Previous…" / "Next…" labels are recognised by default; modules
 * with custom controls declare them under `a11y.carousel` in their manifest.
 */
export const carousel = {
  root: '.swiper',
  slide: '.swiper-slide',
  activeSlide: '.swiper-slide-active, .swiper-slide-visible',
  prev: [
    ...contracts.map((contract) => contract.carousel?.prev).filter((s): s is string => !!s),
    '.swiper-button-prev',
    '[aria-label^="Previous" i]',
  ].join(', '),
  next: [
    ...contracts.map((contract) => contract.carousel?.next).filter((s): s is string => !!s),
    '.swiper-button-next',
    '[aria-label^="Next" i]',
  ].join(', '),
};

/** Tabbable elements, for focus-order checks. */
export const tabbableSelector = [
  'a[href]',
  'area[href]',
  'button:not([disabled])',
  'input:not([disabled]):not([type="hidden"])',
  'select:not([disabled])',
  'textarea:not([disabled])',
  'iframe',
  'audio[controls]',
  'video[controls]',
  '[contenteditable]:not([contenteditable="false"])',
  '[tabindex]:not([tabindex="-1"])',
].join(', ');
