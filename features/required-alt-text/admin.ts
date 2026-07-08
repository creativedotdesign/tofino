/**
 * Required Alt Text — admin enforcement.
 *
 * Makes the WordPress "Alternative Text" field required for images:
 *   1. The Backbone media modal (attachment-details sidebar, incl. the grid's
 *      two-column view) marks alt required, live-validates it, shows an inline
 *      error, and disables the frame's Insert/Select button while a selected
 *      image has empty alt.
 *   2. The single attachment edit screen hard-blocks the Update submit until an
 *      image has alt text.
 *
 * Decorative images are not exempt — every image needs non-empty alt text.
 * Default-exported init invoked on DOMContentLoaded by the admin bundle's
 * feature-script loader (see src/js/core/themeAssets.ts).
 *
 * No jQuery dependency: vanilla DOM throughout, plus the Backbone view's own
 * `this.$el`, narrowed to the small slice of the (otherwise untyped) `wp.media`
 * surface this feature touches. The views are patched on their prototypes
 * (not class replacement) so the grid's pre-defined TwoColumn subclass is
 * covered too.
 */

const MESSAGE = 'Alt text is required for images.';
const STAR = ' <span class="req-alt-star" aria-hidden="true">*</span>';
const ALT_LABEL = '[data-setting="alt"] label, [data-setting="alt"] .name';
const ALT_SETTING = '[data-setting="alt"]';
const PRIMARY_BUTTONS =
  '.media-toolbar-primary .media-button-insert,' +
  '.media-toolbar-primary .media-button-select,' +
  '.media-toolbar-primary .button-primary';

/** Minimal slice of the Backbone `wp.media` surface this feature touches. */
interface AttachmentModel {
  get(attribute: string): string | undefined;
}

interface MediaEl {
  readonly length: number;
  readonly 0?: HTMLElement;
  find(selector: string): MediaEl;
  first(): MediaEl;
  append(content: string): void;
  remove(): void;
  toggleClass(className: string, state: boolean): void;
}

interface DetailsInstance {
  model: AttachmentModel;
  $el: MediaEl;
}

interface DetailsClass {
  prototype: { render?: (this: DetailsInstance) => unknown };
  TwoColumn?: DetailsClass;
}

interface WpMedia {
  media?: { view?: { Attachment?: { Details?: DetailsClass } } };
}

const getMedia = (): WpMedia | undefined => (window as unknown as { wp?: WpMedia }).wp;

/** The alt field's LIVE value (the Backbone model only syncs on `change`). */
const altInputValue = (view: DetailsInstance): string => {
  const field = view.$el[0]?.querySelector<HTMLInputElement | HTMLTextAreaElement>(
    '[data-setting="alt"] input, [data-setting="alt"] textarea',
  );
  return field ? String(field.value || '') : '';
};

/** True when the view's image has empty/whitespace alt text. */
const altMissing = (view: DetailsInstance): boolean =>
  view.model.get('type') === 'image' && altInputValue(view).replace(/\s+/g, '').length === 0;

/** Add the red asterisk to the (Backbone-rendered) Alt Text label once. */
const markRequired = (view: DetailsInstance): void => {
  if (view.model.get('type') !== 'image') {
    return;
  }
  const label = view.$el.find(ALT_LABEL).first();
  if (label.length && !label.find('.req-alt-star').length) {
    label.append(STAR);
  }
};

/** Disable the frame's primary action (Insert/Select) while alt is empty. */
const gateToolbar = (view: DetailsInstance, invalid: boolean): void => {
  const scope: ParentNode = view.$el[0]?.closest('.media-modal') ?? document;
  scope.querySelectorAll<HTMLButtonElement>(PRIMARY_BUTTONS).forEach((button) => {
    button.disabled = invalid;
    button.classList.toggle('disabled', invalid);
  });
};

/** Validate the alt field: toggle the inline error + gate the toolbar. */
const validate = (view: DetailsInstance): void => {
  const invalid = altMissing(view);
  const setting = view.$el.find(ALT_SETTING);

  setting.toggleClass('req-alt-invalid', invalid);

  const message = setting.find('.req-alt-msg');
  if (invalid && !message.length) {
    setting.append(`<span class="req-alt-msg">${MESSAGE}</span>`);
  } else if (!invalid && message.length) {
    message.remove();
  }

  gateToolbar(view, invalid);
};

/**
 * Wrap a details view's `render` so every instance (existing class refs
 * included) marks the field required, validates, and re-validates on input.
 */
const patchView = (View?: DetailsClass): void => {
  const proto = View?.prototype;
  if (!proto) {
    return;
  }
  const originalRender = proto.render;

  proto.render = function reqAltRender(this: DetailsInstance): unknown {
    const result = originalRender?.call(this);
    markRequired(this);
    validate(this);

    const root = this.$el[0];
    if (root && !root.dataset.reqAltBound) {
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

/** Patch the standard details view + the grid's two-column subclass. */
const patchMediaViews = (): boolean => {
  const Details = getMedia()?.media?.view?.Attachment?.Details;
  if (!Details) {
    return false;
  }
  patchView(Details);
  patchView(Details.TwoColumn);
  return true;
};

/** Hard-block the single attachment edit screen's Update form for images. */
const guardAttachmentEditScreen = (): void => {
  const form = document.querySelector<HTMLFormElement>('form#post');
  if (!form || !document.body.classList.contains('post-type-attachment')) {
    return;
  }

  // Images only: the attachment edit screen renders a preview <img> for images.
  if (!document.querySelector('.wp_attachment_image img')) {
    return;
  }

  const postId = new URLSearchParams(window.location.search).get('post');
  const selectors = [
    postId ? `[name="attachments[${postId}][image_alt]"]` : '',
    '[name$="[image_alt]"]',
    '[name="_wp_attachment_image_alt"]',
    '#attachment_alt',
    '#attachment-details-two-column-alt-text',
  ].filter(Boolean);

  const alt = form.querySelector<HTMLTextAreaElement | HTMLInputElement>(selectors.join(', '));
  if (!alt) {
    return;
  }

  // Add the required asterisk to this screen's native alt label (the compat PHP
  // filter only reaches the legacy image_alt field, not #attachment_alt).
  if (alt.id) {
    const label = document.querySelector(`label[for="${alt.id}"]`);
    if (label && !label.querySelector('.req-alt-star')) {
      label.insertAdjacentHTML('beforeend', STAR);
    }
  }

  const isEmpty = (): boolean => String(alt.value || '').replace(/\s+/g, '').length === 0;
  const errorAnchor: HTMLElement = alt.closest('.setting') ?? alt;

  form.addEventListener('submit', (event: SubmitEvent): void => {
    if (!isEmpty()) {
      return;
    }
    event.preventDefault();
    alt.classList.add('req-alt-invalid');
    if (!errorAnchor.parentElement?.querySelector('.req-alt-msg')) {
      errorAnchor.insertAdjacentHTML('afterend', `<span class="req-alt-msg">${MESSAGE}</span>`);
    }
    alt.focus();
    alt.scrollIntoView({ block: 'center' });
  });

  alt.addEventListener('input', (): void => {
    if (!isEmpty()) {
      alt.classList.remove('req-alt-invalid');
      errorAnchor.parentElement?.querySelector('.req-alt-msg')?.remove();
    }
  });
};

const requiredAltText = (): void => {
  if (!patchMediaViews()) {
    // media-views can finish loading a tick later on some screens.
    window.addEventListener('load', patchMediaViews, { once: true });
  }
  guardAttachmentEditScreen();
};

export default requiredAltText;
