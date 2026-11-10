import { execFileSync } from 'node:child_process';
import { readdir } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const themeDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const ignoredDirs = new Set([
  '.cache',
  '.git',
  'build',
  'dist',
  'dist-release',
  'node_modules',
  'vendor',
]);

const phpFiles = [];
/** Collect theme-owned PHP files without traversing generated dependencies. */
const collect = async (directory) => {
  for (const entry of await readdir(directory, { withFileTypes: true })) {
    if (entry.isDirectory() && ignoredDirs.has(entry.name)) {
      continue;
    }
    const file = path.join(directory, entry.name);
    if (entry.isDirectory()) {
      await collect(file);
    }
    if (entry.isFile() && entry.name.endsWith('.php')) {
      phpFiles.push(file);
    }
  }
};

await collect(themeDir);
phpFiles.sort();

for (const file of phpFiles) {
  try {
    execFileSync('php', ['-l', file], { stdio: 'ignore' });
  } catch {
    console.error(`PHP syntax check failed: ${path.relative(themeDir, file)}`);
    process.exit(1);
  }
}

console.log(`PHP syntax valid: ${phpFiles.length} files.`);
