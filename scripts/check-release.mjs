import { execFileSync } from 'node:child_process';
import { mkdtemp, readFile, readdir, rm, stat } from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { build, resolveConfig } from 'vite';

const themeDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const temporaryDir = await mkdtemp(path.join(os.tmpdir(), 'tofino-release-'));
const buildDir = path.join(temporaryDir, 'dist');
const configFile = path.join(themeDir, 'vite.config.ts');

try {
  // Simulate a clean checkout without reading a developer's local .env files.
  process.chdir(temporaryDir);
  process.env.NODE_ENV = 'production';
  process.env.VITE_DEV_PROFILE = 'local';
  delete process.env.VITE_THEME_PATH;

  const config = await resolveConfig(
    { configFile, logLevel: 'error' },
    'build',
    'production',
    'production',
  );
  if (config.base !== './') {
    throw new Error(`A portable release must use a relative base; got ${config.base}`);
  }

  await build({ configFile, logLevel: 'error', build: { outDir: buildDir } });
  const manifest = JSON.parse(await readFile(path.join(buildDir, '.vite/manifest.json'), 'utf8'));

  for (const entry of Object.values(manifest)) {
    for (const file of [entry.file, ...(entry.css ?? []), ...(entry.assets ?? [])]) {
      if (!(await stat(path.join(buildDir, file))).isFile()) {
        throw new Error(`Missing built manifest asset: ${file}`);
      }
    }
  }

  for (const file of await readdir(path.join(buildDir, 'assets'))) {
    if (!/\.(js|css)$/.test(file)) {
      continue;
    }

    const contents = await readFile(path.join(buildDir, 'assets', file), 'utf8');
    if (contents.includes('/undefined/dist/')) {
      throw new Error(`Invalid production asset URL in ${file}`);
    }
  }

  // Package that exact clean build without changing a running local site's dist/.
  execFileSync('bash', [path.join(themeDir, 'scripts/package-theme.sh')], {
    cwd: themeDir,
    env: {
      ...process.env,
      TOFINO_BUILD_DIR: buildDir,
      TOFINO_ARCHIVE_DIR: path.join(themeDir, 'dist-release'),
    },
    stdio: 'inherit',
  });

  console.log('Environment-free production build and release archive verified.');
} finally {
  await rm(temporaryDir, { recursive: true, force: true });
}
