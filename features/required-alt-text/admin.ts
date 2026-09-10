/**
 * Required Alt Text — admin entry.
 *
 * Makes the WordPress "Alternative Text" field required for images. The
 * decorative-image exemption is deliberately not honoured, and WordPress's
 * "Leave empty if the image is purely decorative." hint is stripped.
 *
 *   - alt-field.ts   marks/validates the field in the media modals
 *   - modal-guard.ts intercepts Insert/Select and closing edit-in-place modals
 *   - edit-screen.ts blocks the attachment edit screen's Update
 *
 * Default-exported init invoked on DOMContentLoaded by the admin bundle's
 * feature-script loader (see src/js/core/themeAssets.ts).
 */

import { patchMediaViews } from './alt-field';
import { guardAttachmentEditScreen } from './edit-screen';
import { guardModalActions } from './modal-guard';

const requiredAltText = (): void => {
  if (!patchMediaViews()) {
    // media-views can finish loading a tick later on some screens.
    window.addEventListener('load', patchMediaViews, { once: true });
  }
  guardModalActions();
  guardAttachmentEditScreen();
};

export default requiredAltText;
