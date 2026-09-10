/**
 * Required Alt Text — the alt field itself.
 *
 * Patches the media modals' attachment-details views so an image's
 * "Alternative Text" field is marked required, live-validated, and shows a red
 * inline message while empty. Also provides the shared bits the modal and
 * edit-screen guards build on (message markup, blank check, hint stripping).
 */

import { getWp, type AttachmentModel, type DetailsClass, type DetailsInstance } from './types';

export const MESSAGE = 'Alt text is required for images.';
/** Non-breaking space so the star never wraps onto its own line. */
export const STAR = '<span class="req-alt-star" aria-hidden="true">&nbsp;*</span>';

const ALT_SETTING = '[data-setting="alt"]';
/** Grid view wraps the label text in `.name`; the sidebar's label IS `.name`. */
const ALT_LABEL = `${ALT_SETTING} .name, ${ALT_SETTING} label`;
const ALT_INPUT = `${ALT_SETTING} input, ${ALT_SETTING} textarea`;
const ALT_DESCRIPTION = '#alt-text-description, .attachment-alt-text-description';
/** Also eats the period that closes the preceding "Learn how…" link sentence. */
const DECORATIVE_HINT = /\.?\s*Leave empty if the image is purely decorative\.?/g;

/** Rendered details views keyed by their root element. */
const detailsByEl = new WeakMap<HTMLElement, DetailsInstance>();

export const messageHtml = (): string => `<span class="req-alt-msg" role="alert">${MESSAGE}</span>`;

export const isBlank = (value: string | undefined): boolean =>
  String(value ?? '').replace(/\s+/g, '').length === 0;

const isImage = (model: AttachmentModel): boolean => model.get('type') === 'image';

/** The details view currently shown inside `scope`, if any. */
export const detailsIn = (scope: ParentNode): DetailsInstance | undefined => {
  const el = scope.querySelector<HTMLElement>('.attachment-details');
  return el ? detailsByEl.get(el) : undefined;
};

/** The alt field's LIVE value (the Backbone model only syncs on `change`). */
export const altField = (view: DetailsInstance): HTMLInputElement | HTMLTextAreaElement | null =>
  view.$el[0]?.querySelector<HTMLInputElement | HTMLTextAreaElement>(ALT_INPUT) ?? null;

export const altMissing = (view: DetailsInstance): boolean =>
  isImage(view.model) && isBlank(altField(view)?.value);

/**
 * Multi-select: an earlier pick stays selected when another image is clicked
 * or uploaded, but only the last one is shown in the sidebar. Those can only
 * be read from their models (synced on change/save — fine for "is it empty").
 */
export const hiddenSelectionMissingAlt = (view: DetailsInstance): boolean =>
  (view.controller?.state().get('selection')?.models ?? []).some(
    (model) => model !== view.model && isImage(model) && isBlank(model.get('alt')),
  );

/** Drop WordPress's "Leave empty if decorative" sentence from the description. */
export const stripDecorativeHint = (scope: ParentNode): void => {
  scope.querySelectorAll(ALT_DESCRIPTION).forEach((description) => {
    description.childNodes.forEach((node) => {
      if (node.nodeType === Node.TEXT_NODE) {
        node.textContent = (node.textContent ?? '').replace(DECORATIVE_HINT, '');
      }
    });
  });
};

/** Toggle the red inline error under the alt field. */
export const validate = (view: DetailsInstance): boolean => {
  const invalid = altMissing(view);
  const setting = view.$el.find(ALT_SETTING);
  const message = setting.find('.req-alt-msg');

  setting.toggleClass('req-alt-invalid', invalid);
  if (invalid && !message.length) {
    // Directly under the field (other plugins add controls to the row too).
    altField(view)?.insertAdjacentHTML('afterend', messageHtml());
  } else if (!invalid && message.length) {
    message.remove();
  }
  return invalid;
};

/**
 * Wrap the details views' `render` (prototype, so the grid's pre-defined
 * TwoColumn subclass is covered) to mark the field, validate, and re-validate
 * on input.
 */
const patchDetailsView = (View?: DetailsClass): void => {
  const proto = View?.prototype;
  if (!proto) {
    return;
  }
  const originalRender = proto.render;

  proto.render = function reqAltRender(this: DetailsInstance): unknown {
    const result = originalRender?.call(this);
    const root = this.$el[0];
    if (!root || !isImage(this.model)) {
      return result;
    }

    detailsByEl.set(root, this);
    const label = this.$el.find(ALT_LABEL).first();
    if (label.length && !label.find('.req-alt-star').length) {
      label.append(STAR);
    }
    stripDecorativeHint(root);
    validate(this);

    if (!root.dataset.reqAltBound) {
      root.dataset.reqAltBound = '1';
      root.addEventListener('input', (event: Event): void => {
        if ((event.target as HTMLElement | null)?.closest(ALT_SETTING)) {
          validate(this);
        }
      });
    }
    return result;
  };
};

/** Patch both details views; false when `wp.media` isn't loaded yet. */
export const patchMediaViews = (): boolean => {
  const Details = getWp()?.media?.view?.Attachment?.Details;
  if (!Details) {
    return false;
  }
  patchDetailsView(Details);
  patchDetailsView(Details.TwoColumn);
  return true;
};
