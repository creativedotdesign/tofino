import autosize from 'autosize';

declare const acf: {
  addAction: (action: string, callback: (...args: unknown[]) => void) => void;
};

const MIN_WYSIWYG_HEIGHT = 150;

/**
 * Initialises autosize on all ACF textarea fields on the page.
 * Registers ACF action hooks to keep autosize in sync when fields are
 * shown, resized, or appended dynamically.
 *
 * @returns void
 */
const initTextareas = (): void => {
  const textareas = document.querySelectorAll<HTMLTextAreaElement>(
    '.acf-field:not(.no-autosize) textarea',
  );

  autosize(textareas);

  if (typeof acf !== 'undefined') {
    acf.addAction('ready load resize', () => autosize.update(textareas));
    acf.addAction('show_field', () => setTimeout(() => autosize.update(textareas), 750));
    acf.addAction('append', (el: unknown) => {
      // ACF passes a jQuery-wrapped element; index [0] gives the native DOM node
      const container = (el as ArrayLike<HTMLElement>)[0];
      if (!container) {
        return;
      }
      const newTextareas = container.querySelectorAll<HTMLTextAreaElement>(
        '.acf-field:not(.no-autosize) textarea',
      );
      if (newTextareas.length > 0) {
        autosize(newTextareas);
      }
    });
  }
};

/**
 * Resizes a TinyMCE WYSIWYG editor iframe to fit its content.
 * No-ops when the editor is in fullscreen mode.
 *
 * @param editor - The TinyMCE editor instance, containing the iframe element and plugin references.
 * @returns void
 */
const resizeWysiwyg = (editor: {
  iframeElement: HTMLIFrameElement;
  plugins: { fullscreen?: { isFullscreen: () => boolean } };
}): void => {
  if (editor.plugins.fullscreen?.isFullscreen()) {
    return;
  }

  const iframe = editor.iframeElement;
  const height = iframe.contentDocument?.documentElement.scrollHeight || MIN_WYSIWYG_HEIGHT;

  iframe.style.height = `${Math.max(height, MIN_WYSIWYG_HEIGHT)}px`;
  iframe.style.minHeight = `${MIN_WYSIWYG_HEIGHT}px`;
};

/**
 * Initialises autosize on ACF WYSIWYG fields by hooking into TinyMCE
 * init, change, and fullscreen events via ACF action callbacks.
 *
 * @returns void
 */
const initWysiwyg = (): void => {
  if (typeof acf === 'undefined') {
    return;
  }

  acf.addAction('wysiwyg_quicktags_init', (...args: unknown[]) => {
    const field = args[3] as { $el: { find: (selector: string) => NodeList } };
    autosize(field.$el.find('textarea.wp-editor-area'));
  });

  acf.addAction('wysiwyg_tinymce_init', (...args: unknown[]) => {
    const editor = args[0] as {
      iframeElement: HTMLIFrameElement;
      plugins: { fullscreen?: { isFullscreen: () => boolean } };
      on: (event: string, callback: () => void) => void;
    };
    const field = args[3] as {
      $el: { hasClass: (cls: string) => boolean; attr: (name: string) => string };
    };

    if (field.$el.hasClass('no-autosize')) {
      return;
    }

    const resize = () => resizeWysiwyg(editor);

    editor.on('init', () => {
      const body = editor.iframeElement.contentDocument?.querySelector('body');
      if (body) {
        body.setAttribute('data-wysiwyg-slug', field.$el.attr('data-name'));
      }
      resize();
    });

    editor.on('change', resize);
    editor.on('FullscreenStateChanged', resize);

    acf.addAction('load', resize);
    acf.addAction('resize', resize);
    acf.addAction('show_field', () => setTimeout(resize, 750));

    setTimeout(resize, 1000);
  });
};

/**
 * Initialises ACF autosize for all textarea and WYSIWYG fields on the page.
 * Adds an `acf-autosize-enabled` class to `document.body` once complete.
 *
 * @returns void
 */
export const acfAutosize = (): void => {
  initTextareas();
  initWysiwyg();
  document.body.classList.add('acf-autosize-enabled');
};
