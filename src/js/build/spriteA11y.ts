import fs from 'node:fs';
import path from 'node:path';

/**
 * Marks the emitted SVG sprite as decorative.
 *
 * The sprite is a symbol store that templates reference with <use>; it is
 * not content, yet without `aria-hidden` the root <svg> is exposed to
 * assistive technology (the Carlsbad ADA audit flagged exactly this). The
 * spritemap plugin offers no option for root attributes, so this stamps the
 * written file once the bundle is on disk. Each `<use>` instance is handled
 * at render time by the theme's svg() helper.
 *
 * Typed structurally rather than as Vite's `Plugin` on purpose: child themes
 * import this from the parent checkout and have their own copy of Vite, and
 * comparing two packages' `Plugin` declarations overflows the type checker.
 *
 * @param file Sprite path relative to the build output directory.
 */
const spriteA11y = (file = 'sprite.svg') => {
  let outDir = '';

  return {
    name: 'tofino-sprite-a11y',
    apply: 'build' as const,
    enforce: 'post' as const,
    configResolved(config: { root: string; build: { outDir: string } }) {
      outDir = path.resolve(config.root, config.build.outDir);
    },
    closeBundle() {
      const sprite = path.join(outDir, file);

      if (!outDir || !fs.existsSync(sprite)) {
        return;
      }

      const svg = fs.readFileSync(sprite, 'utf8');

      if (/<svg\b[^>]*\baria-hidden=/.test(svg)) {
        return;
      }

      fs.writeFileSync(sprite, svg.replace(/<svg\b/, '<svg aria-hidden="true" focusable="false"'));
    },
  };
};

export default spriteA11y;
