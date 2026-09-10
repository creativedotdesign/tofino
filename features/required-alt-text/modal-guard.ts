/**
 * Required Alt Text — media modal guards.
 *
 * Rather than fighting WordPress for the state of its buttons, the feature
 * intercepts the action itself (capture phase, ahead of WordPress's handlers):
 *   - Insert into post / Select / Set featured image, while any selected image
 *     has empty alt.
 *   - In edit-in-place modals (the Media Library grid, ACF's "Edit Image"
 *     popup: fields autosave, nothing else to gate), closing (X, backdrop,
 *     Escape) or moving to the previous/next item. Deleting the image is the
 *     way out and is never blocked.
 */

import { altField, altMissing, detailsIn, hiddenSelectionMissingAlt, validate } from './alt-field';
import type { DetailsInstance } from './types';

const SELECTION_MESSAGE = 'Alt text is required for every selected image.';
const PRIMARY_BUTTON =
  '.media-toolbar-primary .media-button-insert, .media-toolbar-primary .media-button-select, .media-toolbar-primary .button-primary';
const GRID_FRAME = '.edit-attachment-frame';
const ACF_EDIT_MODAL = '.acf-media-modal.-edit';
const EDIT_LEAVE = '.media-modal-close, .media-modal-backdrop, .left, .right';

/** The modal an element belongs to (the backdrop sits beside it, not inside). */
const modalFor = (el: HTMLElement): HTMLElement | null =>
  el.closest<HTMLElement>('.media-modal') ??
  el.parentElement?.querySelector<HTMLElement>('.media-modal') ??
  null;

const isEditModal = (modal: HTMLElement): boolean =>
  modal.matches(ACF_EDIT_MODAL) || Boolean(modal.querySelector(GRID_FRAME));

/** The open edit-in-place modal, if any. */
const openEditModal = (): HTMLElement | undefined =>
  Array.from(document.querySelectorAll<HTMLElement>('.media-modal')).find(
    (modal) => isEditModal(modal) && modal.getBoundingClientRect().width > 0,
  );

/** Explain a blocked button when the offending image isn't the one shown. */
const toggleSelectionNote = (view: DetailsInstance, show: boolean): void => {
  // The frame's bottom toolbar — the attachments browser's filter bar is a
  // `.media-toolbar-primary` too.
  const toolbar = view.$el[0]
    ?.closest('.media-modal')
    ?.querySelector('.media-frame-toolbar .media-toolbar-primary');
  const note = toolbar?.querySelector('.req-alt-toolbar-msg');
  if (show && toolbar && !note) {
    toolbar.insertAdjacentHTML(
      'afterbegin',
      `<span class="req-alt-msg req-alt-toolbar-msg">${SELECTION_MESSAGE}</span>`,
    );
  } else if (!show) {
    note?.remove();
  }
};

/** Swallow the action, show the error, and put the cursor in the alt field. */
const block = (event: Event, view: DetailsInstance): void => {
  event.preventDefault();
  event.stopImmediatePropagation();
  validate(view);
  const field = altField(view);
  field?.focus();
  field?.scrollIntoView({ block: 'center' });
};

const onClick = (event: MouseEvent): void => {
  const target = event.target as HTMLElement | null;
  if (!target) {
    return;
  }

  // Insert / Select / Set featured image.
  const button = target.closest<HTMLElement>(PRIMARY_BUTTON);
  const modal = button && modalFor(button);
  const view = modal && detailsIn(modal);
  if (view) {
    const hiddenMissing = hiddenSelectionMissingAlt(view);
    toggleSelectionNote(view, hiddenMissing);
    if (altMissing(view) || hiddenMissing) {
      block(event, view);
    }
    return;
  }

  // Edit-in-place modals: close (X / backdrop) or previous / next.
  const leave = target.closest<HTMLElement>(EDIT_LEAVE);
  const editModal = leave && modalFor(leave);
  const editView = editModal && isEditModal(editModal) ? detailsIn(editModal) : undefined;
  if (editView && altMissing(editView)) {
    block(event, editView);
  }
};

/** Edit-in-place modals: Escape closes, Alt+Arrow navigates the grid. */
const onKeydown = (event: KeyboardEvent): void => {
  const leaving =
    event.key === 'Escape' ||
    (event.altKey && (event.key === 'ArrowLeft' || event.key === 'ArrowRight'));
  const editModal = leaving ? openEditModal() : undefined;
  const view = editModal && detailsIn(editModal);
  if (view && altMissing(view)) {
    block(event, view);
  }
};

export const guardModalActions = (): void => {
  document.addEventListener('click', onClick, true);
  document.addEventListener('keydown', onKeydown, true);
};
