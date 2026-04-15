import path from 'node:path';
import type { Plugin } from 'vite';

type PhpFullReloadOptions = {
  patterns?: string[];
};

/**
 * Watches theme PHP files and triggers a full page reload in development.
 */
const phpFullReload = (options: PhpFullReloadOptions = {}): Plugin => ({
  name: 'php-full-reload',
  apply: 'serve',
  configureServer(server) {
    if (!options.patterns?.length) {
      return;
    }

    const themeRoot = path.resolve(server.config.root, '..');

    for (const pattern of options.patterns) {
      server.watcher.add(path.join(themeRoot, pattern));
    }

    server.watcher.on('change', (file) => {
      if (file.endsWith('.php')) {
        server.ws.send({ type: 'full-reload', path: '*' });
      }
    });
  },
});

export default phpFullReload;
