# Testing

Tofino ships a Playwright QA suite that child themes (Compose, JAX) run against their own site. The generic specs live here; what is theme-specific (menus, carousels, banners) is declared by each module or feature in its manifest, so the parent never learns a child's selectors.

```bash
npm test                 # everything, Chromium
npm run test:smoke       # header/menu/alert behaviour
npm run test:audits      # every sitemap URL: axe, html-validate, overflow, DOM lint, focus
npm run test:overlays    # menus and dialogs behave as modal dialogs
npm run test:carousels   # Swiper instances are keyboard/screen-reader safe
npm run test:webkit      # behavioural specs in WebKit (VoiceOver audits are Safari-based)
npm run test:report      # open the Playwright HTML report
```

From a child theme, `npm run test:a11y` runs the same suite with the parent's config (`playwright test --config ../tofino/playwright.config.ts`). Manifests, `.env` and reports are then taken from the child's directory, falling back to the parent's `.env` for the site URL. Reports land in `tests/output/` of whichever theme you ran from (gitignored): `accessibility-report.html`, `html-validation-report.html`, `overflow-detection-report.html`, `dom-lint-report.html` and `urls.json`.

## Which pages are tested

Sites repeat the same templates across hundreds of URLs, so the routine run audits a curated list rather than the sitemap. Each theme keeps it in `tests/a11y.config.json`:

```json
{ "urls": ["/", "/mega-test/", "/web-apps/", "/news-single-figma-qa/"] }
```

Pick the homepage, a kitchen-sink page carrying every module, one of each single template, the web apps and the forms. The search results page (`/?s=…`) is always added. The list actually audited is written to `tests/output/urls.json` on every run.

| Variable | Effect |
|---|---|
| `A11Y_CRAWL=1` | Crawl the whole sitemap instead of the curated list (also the fallback when no list exists). |
| `A11Y_URLS=/jobs/?q=nurse` | Extra URLs for this run, comma separated. |
| `A11Y_MAX_URLS=5` | Cap the list. The homepage is always first. |
| `A11Y_SCREENSHOTS=1` | Write a full-page screenshot per page with findings, banners dismissed and offending elements outlined. Off by default: the reports carry selectors and HTML snippets. |
| `A11Y_HOVER=1` | Hover every distinct link and button at desktop and run axe on it. Slow, and `audit-ada-mcp --states` does hover and focus contrast better; keep for spot checks. |

Example, checking one page while building a module:

```bash
A11Y_MAX_URLS=1 A11Y_URLS=/communities/ npm run test:dom
```

## Declaring what a module or feature owns

A module or feature with interactive UI describes it under an `a11y` key in its `module.json` or `feature.json`, next to the template and script it already declares. The generic specs read every manifest in the theme the suite runs from, plus `modules/*/module.json` in every sibling plugin (`wp-content/plugins/*/`), which is where plugin modules such as the quiz launcher declare theirs.

```json
"a11y": {
  "overlays": [
    { "name": "Header overlay menu", "trigger": "[data-header-open]", "container": "#header-menu", "close": "[data-header-close]" }
  ],
  "carousel": { "prev": "[data-km-prev]", "next": "[data-km-next]" },
  "dismiss": [".js-popup-close"]
}
```

- `overlays`: anything that must behave as a modal dialog. For repeated dialogs use a selector that matches whichever one is open, e.g. `dialog.community-modal[open]`.
- `carousel`: prev/next controls when a module does not use Swiper's own buttons.
- `dismiss`: cookie banners and popups the behavioural specs click away first.

Native `<dialog>` openers (`button[aria-haspopup="dialog"][aria-controls]`), Swiper's `.swiper-button-*` and controls labelled "Previous…" / "Next…" are found without a declaration. The overlays spec walks the audit URL list and checks each overlay once, on the first page where its trigger is visible, so an overlay that only exists on one page (a quiz launcher) still needs that page in `tests/a11y.config.json`. A menu that is not found at phone width fails the spec rather than being skipped, so a renamed attribute shows up as a red test.

### Third-party embeds

Markup you cannot change (Tableau, ZoomProspector, LocalIntel, video players) is left out of every audit by declaring its container. The data-embed module does this in its manifest:

```json
"a11y": { "exclude": ["[data-embed-target]"] }
```

Everything *inside* an excluded container is skipped by axe, html-validate, DOM lint, overflow and the focus audit; the container element itself is still checked, so an `<iframe>` without a `title` is reported (that attribute is ours). axe also never injects into frames, so cross-origin content is out regardless of declarations. One-off embeds that no module owns (a YouTube iframe in body copy) go in the site config: `{ "urls": [...], "exclude": ["iframe[src*='youtube']"] }`. An ADA auditor will still see those embeds; excluding them keeps the report to what the site can fix.

A module needing assertions beyond that contract (a bespoke keyboard model, say) can keep its own `a11y.spec.ts` in its folder; the `theme` Playwright project picks it up.

## Motion and reveal-on-scroll

The suite runs with animations on, as a visitor sees the site. Before the static audits (axe, html, overflow) `settlePage()` scrolls through the page so lazy images load and once-only reveals have played. The behavioural specs (focus, overlays, carousels) and DOM lint do not scroll first, so the page is in the state a keyboard user meets: the focus audit flags any tab stop that is at opacity 0 when focus arrives, which is what reveal-on-scroll does to focusable content below the fold.

Compose's "Replay Reveals" option re-hides content whenever it scrolls out of view. axe treats opacity 0 as hidden, so with that option on, below-the-fold content is skipped by contrast checks and looks blank in screenshots. Audit with it set to Once (the default).

Each crawl-wide audit walks every URL inside one test, so it sets its own timeout from the URL count instead of relying on the 60s default in `playwright.config.ts`.

## What each spec covers

| Spec | Catches | WCAG |
|---|---|---|
| `audits/accessibility.spec.ts` | axe at 320 / 768 / 1280 px, tags `wcag2a wcag2aa wcag21a wcag21aa wcag22aa best-practice`; re-run inside each open overlay; hover state on desktop with `A11Y_HOVER=1`; `incomplete` results are reported for manual review instead of dropped | most of 1.x, 4.1.2 |
| `audits/html.spec.ts` | html-validate `recommended` plus the `wcag/*` techniques, `no-redundant-role`, `prefer-native-element`, `no-implicit-button-type`, `unique-landmark`, `heading-level`, `hidden-focusable`, `input-missing-label` | 1.3.1, 2.4.6, 4.1.2 |
| `audits/overflow.spec.ts` | Horizontal overflow at 320, 375 and 1280 px | 1.4.10 |
| `audits/dom-lint.spec.ts` | Markup patterns axe cannot judge: breadcrumb not a list, landmark labels that repeat the role, unhidden decorative SVGs, "opens in new window" missing, generic link text, several links per card to one URL, card grids without list semantics, visible label missing from the accessible name, non-breaking hyphens in phone numbers, generic or filename alt text, forms without a status region, autoplaying video without a pause control. Heading order, landmark names, focusables inside aria-hidden, unnamed controls and ungrouped radios are left to axe and html-validate, which already report them | 1.1.1, 1.3.1, 2.2.2, 2.4.4, 2.5.3, 4.1.2, 4.1.3 |
| `audits/focus.spec.ts` | Tabs through every page: each stop must be on screen, must show a visible focus change, must not be entirely covered (sticky header, cookie banner), must not sit inside `aria-hidden`/`inert`, and must not be at opacity 0 when focus arrives | 2.4.3, 2.4.7, 2.4.11 |
| `features/overlays.spec.ts` | Every declared overlay plus any `aria-haspopup="dialog"` opener: focus moves in, container is a `<dialog>` or `role="dialog" aria-modal`, Tab is contained, Escape closes and returns focus to the trigger | 2.1.2, 2.4.3, 4.1.2 |
| `modules/carousels.spec.ts` | Every Swiper: prev/next are named `<button>`s in Previous→Next order, the carousel region is labelled, and after advancing the hidden slides are `inert` (or have no tabbable content) | 2.4.3, 4.1.2 |

`review`-level DOM-lint findings and axe `incomplete` results do not fail the run; they are listed in the reports for a human decision.

## What automation will not catch

Keep these on the manual checklist for every release (about an hour):

- alt text *quality* and whether an image is meaningful or decorative;
- link purpose in context ("Read More" is flagged, but a *good* name still needs judgement);
- whether an autoplaying video is decorative;
- focus-ring and hover contrast per brand palette: use `audit-ada-mcp --states`, which forces `:hover`/`:focus` and measures 1.4.11 / 2.4.7 / 2.4.11;
- keyboard-only pass of every template, VoiceOver in Safari on the homepage and one of each template, 200% zoom, text-spacing bookmarklet, `prefers-reduced-motion`.

## Vue components

`eslint-plugin-vuejs-accessibility` runs in the ESLint config (`npm run lint`): alt text, labels, click handlers on non-interactive elements, ARIA roles and properties. Plugins with their own Vue builds (dci-quiz-app, form-builder) need the same plugin in their own config.

## Definition of done

The PR template (`.github/PULL_REQUEST_TEMPLATE.md`) carries the accessibility checklist for new modules and features. It comes from the Carlsbad ADA audit (Feb 2026), where a dozen repeated patterns produced 160 findings.
