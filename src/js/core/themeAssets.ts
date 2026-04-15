/**
 * ESM module shape returned by Vite dynamic imports for feature/module scripts.
 */
type ScriptModule = { default?: () => void };

/**
 * Async loader for a script module.
 */
type ScriptLoader = () => Promise<ScriptModule>;

/**
 * Re-keys Vite glob import maps by slug captured from a file path regex.
 *
 * @param glob Glob map where keys are file paths.
 * @param re Regex with first capture group as slug.
 * @returns Map keyed by slug with original values.
 */
const keyBySlug = <T>(glob: Record<string, T>, re: RegExp): Record<string, T> =>
  Object.fromEntries(
    Object.entries(glob).flatMap(([path, loader]) => {
      const match = path.match(re);
      return match ? [[match[1], loader]] : [];
    }),
  );

/**
 * Module script loaders keyed by module slug.
 */
export const moduleScripts: Record<string, ScriptLoader> = keyBySlug(
  import.meta.glob<ScriptModule>('../../../modules/*/script.ts'),
  /\/modules\/([^/]+)\/script\.ts$/,
);

/**
 * Frontend feature script loaders keyed by feature slug.
 */
export const frontendFeatureScripts: Record<string, ScriptLoader> = keyBySlug(
  import.meta.glob<ScriptModule>('../../../features/*/script.ts'),
  /\/features\/([^/]+)\/script\.ts$/,
);

/**
 * Admin feature script loaders keyed by feature slug.
 */
export const adminFeatureScripts: Record<string, ScriptLoader> = keyBySlug(
  import.meta.glob<ScriptModule>('../../../features/*/admin.ts'),
  /\/features\/([^/]+)\/admin\.ts$/,
);
