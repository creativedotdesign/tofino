/** Group tracks by their owning carousel wrapper or module section. */
export function tagCarouselScopes(rootSelector: string): number {
  const scopes = new Set<Element>();

  document.querySelectorAll<HTMLElement>(rootSelector).forEach((root) => {
    if (root.offsetParent === null) {
      return;
    }

    scopes.add(
      root.closest('[data-component="carousel"], [data-module], section, .module') ??
        root.parentElement ??
        root,
    );
  });

  [...scopes].forEach((scope, index) => {
    scope.setAttribute('data-carousel-scope', String(index));
  });

  return scopes.size;
}

/** Check names on exposed tracks and their ancestors inside the carousel scope. */
export function hasCarouselLabel(scope: Element, rootSelector: string): boolean {
  const tracks = scope.matches(rootSelector) ? [scope] : [...scope.querySelectorAll(rootSelector)];
  const exposed = tracks.filter((track) => !track.closest('[aria-hidden="true"], [inert]'));

  return exposed.every((track) => {
    for (
      let element: Element | null = track;
      element && scope.contains(element);
      element = element.parentElement
    ) {
      if (element.getAttribute('aria-label')?.trim()) {
        return true;
      }

      const references = element.getAttribute('aria-labelledby')?.trim().split(/\s+/) ?? [];
      if (references.some((id) => element!.ownerDocument.getElementById(id)?.textContent?.trim())) {
        return true;
      }
    }

    return false;
  });
}
