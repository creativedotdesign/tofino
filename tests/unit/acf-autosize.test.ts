import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { acfAutosize } from '../../src/js/admin/acfAutosize';

vi.mock('autosize', () => ({ default: vi.fn() }));

describe('ACF WYSIWYG autosize lifecycle', () => {
  const actions = new Map<string, (...args: unknown[]) => void>();

  beforeEach(() => {
    vi.useFakeTimers();
    actions.clear();
    vi.stubGlobal('document', {
      querySelectorAll: () => [],
      body: { classList: { add: vi.fn() } },
    });
    vi.stubGlobal('acf', {
      addAction: (name: string, callback: (...args: unknown[]) => void) => {
        actions.set(name, callback);
      },
    });
    acfAutosize();
  });

  afterEach(() => {
    vi.clearAllTimers();
    vi.useRealTimers();
    vi.unstubAllGlobals();
  });

  it('tolerates resize callbacks before initialization and after iframe removal', () => {
    const editor = {
      iframeElement: undefined as unknown,
      plugins: {},
      on: vi.fn(),
    };
    const field = { $el: { hasClass: () => false } };

    actions.get('wysiwyg_tinymce_init')?.(editor, undefined, undefined, field);
    const resize = actions.get('resize');

    expect(resize).toBeTypeOf('function');
    expect(() => resize?.()).not.toThrow();

    const iframe = {
      contentDocument: { documentElement: { offsetHeight: 900 } } as object | null,
      style: { height: '', minHeight: '' },
    };

    editor.iframeElement = iframe;
    resize?.();
    expect(iframe.style.height).toBe('900px');

    iframe.contentDocument = null;
    expect(() => resize?.()).not.toThrow();
    editor.iframeElement = undefined;
    expect(() => vi.runAllTimers()).not.toThrow();
  });

  it('shrinks short content and leaves fullscreen sizing to TinyMCE', () => {
    let fullscreen = false;
    const iframe = {
      contentDocument: { documentElement: { offsetHeight: 900 } },
      style: { height: '', minHeight: '' },
    };
    const editor = {
      iframeElement: iframe,
      plugins: { fullscreen: { isFullscreen: () => fullscreen } },
      on: vi.fn(),
    };
    const field = { $el: { hasClass: () => false } };

    actions.get('wysiwyg_tinymce_init')?.(editor, undefined, undefined, field);
    const resize = actions.get('resize');

    resize?.();
    expect(iframe.style.height).toBe('900px');
    iframe.contentDocument.documentElement.offsetHeight = 56;
    resize?.();
    expect(iframe.style.height).toBe('150px');
    expect(iframe.style.minHeight).toBe('150px');

    fullscreen = true;
    iframe.contentDocument.documentElement.offsetHeight = 1200;
    resize?.();
    expect(iframe.style.height).toBe('150px');
  });
});
