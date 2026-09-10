/**
 * Required Alt Text — the single attachment edit screen (post.php).
 *
 * Hard-blocks the Update submit for images until alt text is filled in, and
 * releases the submit buttons post.js disables on the way in so the user can
 * retry once it is.
 */

import { STAR, isBlank, messageHtml, stripDecorativeHint } from './alt-field';
import { getWp } from './types';

/**
 * Undo what post.js does on the way into a submit (disables the submit
 * buttons, shows the spinner, suspends autosave) — its click handler ignores
 * buttons carrying `.disabled`, so a blocked submit would otherwise strand the
 * user until they reload.
 */
const releaseSubmitButtons = (): void => {
  document
    .querySelectorAll('#submitpost .disabled, #submitpost .spinner.is-active')
    .forEach((el) => el.classList.remove('disabled', 'is-active'));
  getWp()?.autosave?.server?.resume?.();
};

export const guardAttachmentEditScreen = (): void => {
  const form = document.querySelector<HTMLFormElement>('body.post-type-attachment form#post');
  const alt = form?.querySelector<HTMLTextAreaElement>('#attachment_alt');
  // Images only: the attachment edit screen renders a preview <img> for images.
  if (!form || !alt || !document.querySelector('.wp_attachment_image img')) {
    return;
  }

  const label = document.querySelector(`label[for="${alt.id}"]`);
  if (label && !label.querySelector('.req-alt-star')) {
    label.insertAdjacentHTML('beforeend', STAR);
  }
  stripDecorativeHint(form);

  const showError = (show: boolean): void => {
    alt.classList.toggle('req-alt-invalid', show);
    const message = alt.parentElement?.querySelector('.req-alt-msg');
    if (show && !message) {
      alt.insertAdjacentHTML('afterend', messageHtml());
    } else if (!show) {
      message?.remove();
    }
  };

  form.addEventListener('submit', (event: SubmitEvent): void => {
    if (!isBlank(alt.value)) {
      return;
    }
    event.preventDefault();
    releaseSubmitButtons();
    showError(true);
    alt.focus();
    alt.scrollIntoView({ block: 'center' });
  });
  alt.addEventListener('input', (): void => {
    if (!isBlank(alt.value)) {
      showError(false);
    }
  });
};
