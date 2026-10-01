/**
 * DOM lint: markup patterns an accessibility auditor flags that neither axe
 * (WCAG + best-practice tags) nor html-validate's WCAG rules can judge. Runs
 * inside the page via page.evaluate(), so `domLint` must stay a single
 * self-contained function with no imports.
 *
 * Each rule maps to the WCAG success criterion it supports. `error` rules are
 * defects an auditor would report; `review` rules need a human decision
 * (e.g. an autoplaying video may be decorative).
 *
 * Rules are drawn from the Carlsbad ADA audit (Feb 2026). Deliberately NOT
 * duplicated here because another tool in the suite already reports them:
 * heading order (axe heading-order), unlabelled or duplicate landmarks (axe
 * landmark-unique, html-validate unique-landmark), focusable content inside
 * aria-hidden (axe aria-hidden-focus), controls with no name (axe
 * button-name / link-name), radio groups without fieldset (html-validate
 * wcag/h71) and alt text repeating link text (axe image-redundant-alt).
 */

/** One DOM-lint finding, serialisable so it can cross the page.evaluate() boundary. */
export type DomLintFinding = {
  /** Rule id, kebab-case, stable so reports can be diffed between runs. */
  rule: string;
  /** `error` fails the run; `review` is reported for a human decision. */
  level: 'error' | 'review';
  /** WCAG success criterion the rule supports, e.g. "1.3.1". */
  sc: string;
  /** Short CSS path to the offending element. */
  selector: string;
  /** What is wrong and how to fix it. */
  detail: string;
};

/**
 * Lints the current document for accessibility markup patterns.
 *
 * @param exclude Selectors of third-party containers (manifest/site `exclude`)
 *                whose contents are not the site's to fix.
 * @returns Findings in document order per rule; empty when the page is clean.
 */
export const domLint = (exclude: string[] = []): DomLintFinding[] => {
  const findings: DomLintFinding[] = [];

  /** Third-party containers: their descendants are skipped, the container itself is linted. */
  const excludeRoots = exclude.flatMap((selector) => {
    try {
      return Array.from(document.querySelectorAll(selector));
    } catch {
      return [];
    }
  });
  const inExcluded = (el: Element): boolean =>
    excludeRoots.some((root) => root !== el && root.contains(el));

  /**
   * Builds a short, readable CSS path (at most five levels) for reporting.
   * State classes (is-*, has-*, swiper-slide-*, compose-reveal*) are omitted
   * so the path stays stable between runs.
   */
  const selectorFor = (el: Element): string => {
    const parts: string[] = [];
    let node: Element | null = el;

    while (node && node.nodeType === 1 && parts.length < 5) {
      let part = node.tagName.toLowerCase();

      if (node.id) {
        parts.unshift(`${part}#${CSS.escape(node.id)}`);
        break;
      }

      const cls = Array.from(node.classList)
        .filter((c) => !/^(is-|has-|swiper-slide-|compose-reveal)/.test(c))
        .slice(0, 2)
        .map((c) => `.${CSS.escape(c)}`)
        .join('');
      part += cls;

      const parent: Element | null = node.parentElement;
      if (parent) {
        const siblings = Array.from(parent.children).filter((s) => s.tagName === node!.tagName);
        if (siblings.length > 1) {
          part += `:nth-of-type(${siblings.indexOf(node) + 1})`;
        }
      }

      parts.unshift(part);
      node = parent;
    }

    return parts.join(' > ');
  };

  /** Records a finding against an element. */
  const add = (
    rule: string,
    level: 'error' | 'review',
    sc: string,
    el: Element,
    detail: string,
  ): void => {
    findings.push({ rule, level, sc, selector: selectorFor(el), detail });
  };

  /** True when the element or an ancestor is hidden from everyone (display, visibility, hidden, inert, aria-hidden). */
  const isHidden = (el: Element): boolean => {
    if (inExcluded(el)) return true;
    if (el.closest('[aria-hidden="true"], [hidden], [inert]')) return true;
    // No rendered box: display:none on the element OR any ancestor (a closed
    // <dialog>, a collapsed panel). visibility:hidden keeps boxes, so check it.
    if (el.getClientRects().length === 0) return true;
    return window.getComputedStyle(el).visibility === 'hidden';
  };

  /** Collapsed, trimmed text content. */
  const text = (el: Element): string => (el.textContent ?? '').replace(/\s+/g, ' ').trim();

  /**
   * Approximate accessible name: aria-label, then aria-labelledby, then the
   * subtree text with aria-hidden parts removed and image alt / SVG titles
   * substituted. Close enough to the accname algorithm for these rules.
   */
  const accName = (el: Element): string => {
    const label = el.getAttribute('aria-label');
    if (label && label.trim()) return label.trim();

    const labelledBy = el.getAttribute('aria-labelledby');
    if (labelledBy) {
      const names = labelledBy
        .split(/\s+/)
        .map((id) => document.getElementById(id))
        .filter((n): n is HTMLElement => !!n)
        .map(text);
      if (names.join(' ').trim()) return names.join(' ').trim();
    }

    const clone = el.cloneNode(true) as Element;
    clone.querySelectorAll('[aria-hidden="true"]').forEach((n) => n.remove());
    clone.querySelectorAll('img[alt]').forEach((img) => {
      img.replaceWith(document.createTextNode(` ${img.getAttribute('alt')} `));
    });
    clone.querySelectorAll('svg title').forEach((t) => {
      t.replaceWith(document.createTextNode(` ${t.textContent} `));
    });

    return text(clone);
  };

  /** Filters a node list down to elements that are not hidden. */
  const visible = <T extends Element>(list: Iterable<T>): T[] =>
    Array.from(list).filter((el) => !isHidden(el));

  // 1. Breadcrumb trail must be a list (1.3.1). Yoast's default output is spans.
  const crumbNavs = new Set<Element>();
  document
    .querySelectorAll(
      'nav[aria-label*="breadcrumb" i], .breadcrumb, .breadcrumbs, [class*="breadcrumb"]',
    )
    .forEach((el) => {
      const nav = el.closest('nav, [role="navigation"]');
      if (nav && !isHidden(nav)) crumbNavs.add(nav);
    });
  crumbNavs.forEach((nav) => {
    if (!nav.querySelector('ol, ul')) {
      add(
        'breadcrumb-not-list',
        'error',
        '1.3.1',
        nav,
        'Breadcrumb trail is not an <ol>/<ul> of <li> items.',
      );
    }
    if (!nav.querySelector('[aria-current="page"]') && nav.querySelectorAll('a').length > 0) {
      add('breadcrumb-no-current', 'review', '2.4.8', nav, 'No crumb carries aria-current="page".');
    }
  });

  // 2. Landmark labels must not repeat the landmark's OWN role (4.1.2):
  // "Primary", not "Primary Navigation". A nav labelled "Footer" is fine —
  // footer is the parent landmark, not this one's role.
  const roleWords: Array<[string, RegExp]> = [
    ['nav, [role="navigation"]', /\b(navigation|nav)\b/i],
    ['[role="region"], section[aria-label]', /\b(region|section|landmark)\b/i],
    ['aside, [role="complementary"]', /\b(complementary|aside)\b/i],
    ['footer, [role="contentinfo"]', /\b(footer|contentinfo)\b/i],
    ['header, [role="banner"]', /\b(banner|header)\b/i],
    ['main, [role="main"]', /\b(main)\b/i],
  ];
  roleWords.forEach(([selector, pattern]) => {
    document.querySelectorAll(selector).forEach((el) => {
      if (inExcluded(el)) return;
      const label = el.getAttribute('aria-label');
      if (label && pattern.test(label)) {
        add(
          'landmark-label-redundant',
          'error',
          '4.1.2',
          el,
          `aria-label "${label}" repeats the landmark role; screen readers announce it twice.`,
        );
      }
    });
  });

  // 3. Control labels must not contain "button" / "link" (4.1.2).
  visible(document.querySelectorAll('button, [role="button"], a, [role="link"]')).forEach((el) => {
    const label = el.getAttribute('aria-label');
    if (label && /\b(button|link)\b/i.test(label)) {
      add(
        'control-label-redundant',
        'error',
        '4.1.2',
        el,
        `aria-label "${label}" includes the role word; the role is already announced.`,
      );
    }
  });

  // 4. Visible label must be part of the accessible name (2.5.3). axe only
  // ships this as an experimental rule outside every tag set.
  visible(
    document.querySelectorAll('button[aria-label], a[aria-label], [role="button"][aria-label]'),
  ).forEach((el) => {
    const label = (el.getAttribute('aria-label') ?? '').toLowerCase().replace(/\s+/g, ' ').trim();
    const shown = text(el).toLowerCase();
    if (shown && label && !label.includes(shown)) {
      add(
        'label-not-in-name',
        'error',
        '2.5.3',
        el,
        `Visible text "${shown}" is not contained in aria-label "${label}".`,
      );
    }
  });

  // 5. Inline SVGs inside controls must be hidden from AT; standalone SVGs must be hidden or labelled (1.1.1).
  document.querySelectorAll('svg').forEach((svg) => {
    if (isHidden(svg)) return;
    if (svg.getAttribute('aria-hidden') === 'true') return;
    const role = svg.getAttribute('role');
    const labelled =
      svg.hasAttribute('aria-label') ||
      svg.hasAttribute('aria-labelledby') ||
      !!svg.querySelector(':scope > title');
    const inControl = svg.closest('a, button, [role="button"], [role="link"]');

    if (inControl) {
      add(
        'svg-in-control-not-hidden',
        'error',
        '1.1.1',
        svg,
        'Decorative SVG inside a link/button needs aria-hidden="true" focusable="false"; label the control instead.',
      );
    } else if (role !== 'img' || !labelled) {
      add(
        'svg-unhidden',
        'error',
        '1.1.1',
        svg,
        'Inline SVG is neither aria-hidden="true" nor role="img" with a label.',
      );
    }
  });

  // 6. Links opening a new window must say so (3.2.5 / 2.4.4).
  visible(document.querySelectorAll('a[target="_blank"]')).forEach((a) => {
    const name = accName(a);
    if (!/new (window|tab)/i.test(name)) {
      add(
        'new-window-unannounced',
        'error',
        '2.4.4',
        a,
        `Link "${name || '(no name)'}" opens a new window without saying so in its accessible name.`,
      );
    }
  });

  // 7. Links that are really buttons (4.1.2). `review`: placeholder "#" menu
  // items are usually content, but a "#" link that opens a modal is a defect.
  visible(document.querySelectorAll('a[href="#"], a[href=""], a:not([href])')).forEach((a) => {
    add(
      'link-without-destination',
      'review',
      '4.1.2',
      a,
      'Anchor has no real destination; if it triggers an action use <button>, otherwise give it a URL.',
    );
  });

  // 8. Phone numbers with non-breaking hyphens are read as one integer by VoiceOver (1.3.1).
  document.querySelectorAll('a[href^="tel:"]').forEach((a) => {
    if (inExcluded(a)) return;
    if (/[‐‑‒–]/.test(a.textContent ?? '')) {
      add(
        'tel-nonbreaking-hyphen',
        'error',
        '1.3.1',
        a,
        'Phone number uses a non-breaking or typographic hyphen; use plain hyphens or spaces.',
      );
    }
  });

  // 9. Generic, auto-generated or filename alt text (1.1.1). Missing alt is axe image-alt.
  visible(document.querySelectorAll('img')).forEach((img) => {
    const alt = img.getAttribute('alt');
    if (alt === null) return;
    const trimmed = alt.trim();
    if (!trimmed) return;
    if (
      /^(featured image|image of|picture of|photo of|graphic of|icon|logo|image|photo|img)\b/i.test(
        trimmed,
      ) &&
      trimmed.length < 40
    ) {
      add(
        'img-alt-generic',
        'error',
        '1.1.1',
        img,
        `alt="${trimmed}" is generic or auto-generated.`,
      );
    } else if (
      /\.(jpe?g|png|webp|gif|svg)$/i.test(trimmed) ||
      /^(dsc|img)[-_ ]?\d+/i.test(trimmed)
    ) {
      add('img-alt-filename', 'error', '1.1.1', img, `alt="${trimmed}" looks like a filename.`);
    } else if (trimmed.length > 150 || /https?:\/\//i.test(trimmed)) {
      add(
        'img-alt-too-long',
        'review',
        '1.1.1',
        img,
        `alt is ${trimmed.length} characters${/https?:\/\//i.test(trimmed) ? ' and contains a URL' : ''}; keep alt short and move detail to a caption.`,
      );
    }
  });

  // 10. Repeated cards must be list items (1.3.1). Heuristic: 3+ same-shaped siblings, each with a heading and a link.
  const seenGrids = new Set<Element>();
  document
    .querySelectorAll('article, .card, [class*="card"], [class*="tile"], [class*="item"]')
    .forEach((el) => {
      const parent = el.parentElement;
      if (!parent || seenGrids.has(parent) || isHidden(el)) return;
      if (parent.closest('ul, ol, [role="list"], table, nav, select, .swiper-wrapper')) return;
      // Tab panels are a tablist's content, not a list of cards.
      if (
        el.getAttribute('role') === 'tabpanel' ||
        parent.querySelector(':scope > [role="tabpanel"]')
      )
        return;
      const siblings = Array.from(parent.children).filter(
        (s) => s.tagName === el.tagName && s.className === el.className,
      );
      if (siblings.length < 3) return;
      const shaped = siblings.filter(
        (s) => s.querySelector('h2, h3, h4, h5') && s.querySelector('a[href]'),
      );
      if (shaped.length === siblings.length) {
        seenGrids.add(parent);
        add(
          'card-grid-not-list',
          'error',
          '1.3.1',
          parent,
          `${siblings.length} repeated ${el.tagName.toLowerCase()} cards are not marked up as <ul>/<li> (or role="list"/"listitem").`,
        );
      }
    });

  // 11. Several links to one URL inside a card, and generic link text (2.4.4).
  const generic =
    /^(learn more|read more|read story|read the story|click here|more|here|start|email|call|phone|view|details|see more|go|link|download|visit|back)$/i;
  const cardScopes = new Set<Element>();
  const cardSelector = 'article, li, .card, [class*="card"]';
  document.querySelectorAll(cardSelector).forEach((scope) => {
    if (isHidden(scope) || scope.closest('nav')) return;
    // Only the outermost card: article.card and its .card__body would both match.
    if (scope.parentElement?.closest(cardSelector)) return;
    const links = Array.from(scope.querySelectorAll('a[href]')).filter(
      (a) =>
        !isHidden(a) &&
        a.getAttribute('tabindex') !== '-1' &&
        !/#$/.test((a as HTMLAnchorElement).href) &&
        a.closest(cardSelector) === scope,
    );
    if (links.length < 2) return;
    const byHref = new Map<string, Element[]>();
    links.forEach((a) => {
      const href = (a as HTMLAnchorElement).href;
      byHref.set(href, [...(byHref.get(href) ?? []), a]);
    });
    byHref.forEach((group, href) => {
      if (group.length < 2 || cardScopes.has(scope)) return;
      const names = new Set(group.map((a) => accName(a).toLowerCase()));
      if (names.size > 1) {
        cardScopes.add(scope);
        add(
          'duplicate-links-in-card',
          'error',
          '2.4.4',
          scope,
          `${group.length} links to ${href} with different names (${[...names].map((n) => `"${n}"`).join(', ')}). Use one link per destination.`,
        );
      }
    });
  });
  visible(document.querySelectorAll('a[href]')).forEach((a) => {
    const name = accName(a);
    if (generic.test(name) && !a.hasAttribute('aria-describedby')) {
      add(
        'link-text-generic',
        'error',
        '2.4.4',
        a,
        `Link text "${name}" is ambiguous out of context; include the destination (or add aria-describedby).`,
      );
    }
  });

  // 12. More than one h1 (1.3.1). Heading order itself is axe heading-order.
  visible(document.querySelectorAll('h1'))
    .slice(1)
    .forEach((h) => add('multiple-h1', 'review', '1.3.1', h, 'More than one <h1> on the page.'));

  // 13. Autoplaying media without a pause control (2.2.2). Checks playback
  // state, so it also catches videos started by script with no autoplay attribute.
  document.querySelectorAll('video').forEach((video) => {
    if (inExcluded(video) || video.controls) return;
    const playing = !video.paused && !video.ended;
    if (!playing && !video.autoplay) return;
    const scope = video.closest('[data-module], section, header, main') ?? document.body;
    const pause = Array.from(scope.querySelectorAll('button')).some((b) =>
      /pause|play|stop/i.test(accName(b)),
    );
    if (!pause) {
      add(
        'video-autoplay-no-pause',
        'review',
        '2.2.2',
        video,
        'Video plays automatically with no pause/stop control. Decorative and under 5s is fine; otherwise add a pause button.',
      );
    }
  });

  // 14. Status regions must exist on load (4.1.3). Forms that inject a success message later are silent.
  visible(document.querySelectorAll('form')).forEach((form) => {
    if (form.getAttribute('role') === 'search') return;
    const hasStatus =
      form.querySelector('[role="status"], [role="alert"], [aria-live]') ||
      form.parentElement?.querySelector('[role="status"], [role="alert"], [aria-live]');
    if (!hasStatus) {
      add(
        'form-no-status-region',
        'review',
        '4.1.3',
        form,
        'Form has no role="status"/aria-live region in the DOM; success and error messages will not be announced.',
      );
    }
  });

  // 15. Vague button names (2.4.6). Empty names are axe button-name.
  visible(document.querySelectorAll('button')).forEach((el) => {
    const name = accName(el);
    if (/^(toggle|toggle navigation|toggle menu|start|menu|icon|button|submit)$/i.test(name)) {
      add(
        'control-name-vague',
        'review',
        '2.4.6',
        el,
        `Button named "${name}"; say what it opens or does (e.g. "Open main menu").`,
      );
    }
  });

  // 16. Frames need a title (4.1.2; axe frame-title, which the exclusions
  // would otherwise silence). The frame's contents are its provider's; the
  // title on our <iframe> is ours.
  visible(document.querySelectorAll('iframe')).forEach((frame) => {
    if (!(frame.getAttribute('title') ?? '').trim() && !frame.hasAttribute('aria-label')) {
      add(
        'iframe-no-title',
        'error',
        '4.1.2',
        frame,
        'Frame has no title; screen readers announce it as "frame". Say what it holds, e.g. title="Cost of living comparison".',
      );
    }
  });

  return findings;
};
