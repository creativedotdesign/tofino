/**
 * Required Alt Text — the narrow slice of the (untyped) `wp` global this
 * feature touches: the Backbone attachment-details views in the media modals
 * and the autosave server on the attachment edit screen.
 */

export interface AttachmentModel {
  get(attribute: string): string | undefined;
}

/** A jQuery-like element wrapper as exposed by Backbone views' `$el`. */
export interface MediaEl {
  readonly length: number;
  readonly 0?: HTMLElement;
  find(selector: string): MediaEl;
  first(): MediaEl;
  append(content: string): void;
  remove(): void;
  toggleClass(className: string, state: boolean): void;
}

export interface DetailsInstance {
  model: AttachmentModel;
  $el: MediaEl;
  /** The frame; its current state holds the selection. */
  controller?: { state(): { get(attribute: string): { models?: AttachmentModel[] } | undefined } };
}

export interface DetailsClass {
  prototype: { render?: (this: DetailsInstance) => unknown };
  TwoColumn?: DetailsClass;
}

export interface Wp {
  media?: { view?: { Attachment?: { Details?: DetailsClass } } };
  autosave?: { server?: { resume?: () => void } };
}

export const getWp = (): Wp | undefined => (window as unknown as { wp?: Wp }).wp;
