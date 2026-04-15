import type { ThemeScript } from '@/js/shared/types/types';

type ScriptModule = {
  default?: () => void;
};

type ModuleLoader = () => Promise<ScriptModule>;

// Eagerly resolved glob of all frontend scripts (Vite handles code-splitting).
const themeModules = import.meta.glob<ScriptModule>('../frontend/*.ts');

/**
 * Dynamically imports a script module and invokes its default export.
 *
 * @param label - Identifier used in error logging.
 * @param load - Lazy loader that returns the script module.
 */
const runScript = async (label: string, load: ModuleLoader): Promise<void> => {
  try {
    const mod = await load();
    mod.default?.();
  } catch (error) {
    console.error(`Failed to load ${label}:`, error);
  }
};

/**
 * Conditionally loads theme scripts from `src/js/frontend/` when their
 * matching DOM selector is present on the page.
 *
 * @param scripts - Array of selector/src pairs defined in app.ts.
 */
export const loadThemeScripts = async (scripts: ThemeScript[]): Promise<void> => {
  await Promise.all(
    scripts.map(async ({ selector, src }) => {
      // Skip if no matching element exists in the DOM
      if (!document.querySelector(selector)) {
        return;
      }

      // Dynamically import the script module from the glob based on the provided src
      const load = themeModules[`../frontend/${src}.ts`];

      if (!load) {
        console.error(`Failed to resolve theme script ${src}.`);
        return;
      }

      await runScript(src, load);
    }),
  );
};

/**
 * Conditionally loads feature/module scripts when a matching
 * `data-{dataAttr}="{slug}"` element exists in the DOM.
 *
 * @param scripts - Slug-keyed lazy loaders (see `@/js/core/themeAssets`).
 * @param dataAttr - The data attribute name to match (e.g. 'feature' or 'module').
 */
export const loadManifestScripts = async (
  scripts: Record<string, ModuleLoader>,
  dataAttr: string,
): Promise<void> => {
  await Promise.all(
    Object.entries(scripts).map(async ([slug, load]) => {
      if (!document.querySelector(`[data-${dataAttr}="${slug}"]`)) {
        return;
      }

      await runScript(`${dataAttr}/${slug}`, load);
    }),
  );
};
