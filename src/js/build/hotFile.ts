import type { Plugin, ResolvedConfig } from 'vite';
import fs from 'node:fs';
import path from 'node:path';

type HotFileOptions = {
  preferNetworkUrl?: boolean;
  publicUrl?: string;
};

const OWNERS_KEY = Symbol.for('tofino.hot-file.owners');

/**
 * Vite plugin that writes the dev server URL to `{outDir}/hot` when the dev
 * server starts, and removes it on shutdown. WordPress reads this file to
 * detect the dev server without making an HTTP request.
 * Tunnel mode also writes `hot.local` for requests made directly to Local.
 *
 * @returns A Vite plugin object.
 */
const hotFile = (options: HotFileOptions = {}): Plugin => {
  let outDir: string;

  return {
    name: 'hot-file',
    /**
     * Captures the resolved output directory from the Vite config.
     *
     * @param config - The fully resolved Vite configuration object.
     * @returns void
     */
    configResolved(config: ResolvedConfig) {
      outDir = config.build.outDir;
    },
    /**
     * Writes the hot file on dev server start and registers process signal
     * handlers to ensure it is cleaned up on shutdown.
     *
     * @param server - The Vite dev server instance.
     * @returns void
     */
    configureServer(server) {
      const hotPath = path.join(outDir, 'hot');
      const localHotPath = `${hotPath}.local`;
      const globalState = globalThis as Record<symbol, Map<string, symbol>>;
      const owners = (globalState[OWNERS_KEY] ??= new Map());
      const owner = Symbol('hot-file-owner');
      let closed = false;

      owners.set(hotPath, owner);

      server.httpServer?.once('listening', () => {
        if (closed) {
          return;
        }

        const localAddress = server.resolvedUrls?.local[0];
        const networkAddress = server.resolvedUrls?.network[0];
        const protocol = server.config.server.https ? 'https' : 'http';
        const fallbackAddress = `${protocol}://localhost:${server.config.server.port}`;
        const explicitPublicUrl = options.publicUrl?.trim().replace(/\/+$/, '');

        const address =
          explicitPublicUrl ||
          (options.preferNetworkUrl ? networkAddress : localAddress) ||
          localAddress ||
          networkAddress ||
          fallbackAddress;

        fs.mkdirSync(path.dirname(hotPath), { recursive: true });
        fs.writeFileSync(hotPath, address);
        if (explicitPublicUrl) {
          fs.writeFileSync(localHotPath, localAddress || fallbackAddress);
        } else if (fs.existsSync(localHotPath)) {
          fs.unlinkSync(localHotPath);
        }
      });

      /** Remove hot files only if a replacement server has not taken ownership. */
      const cleanup = (): void => {
        // A replacement server may already own these paths during a restart.
        if (owners.get(hotPath) !== owner) {
          return;
        }

        owners.delete(hotPath);
        for (const file of [hotPath, localHotPath]) {
          if (fs.existsSync(file)) {
            fs.unlinkSync(file);
          }
        }
      };

      /** Clean up the owned hot files before exiting on a process signal. */
      const onSignal = (): void => {
        cleanup();
        process.exit();
      };

      const closeServer = server.close.bind(server);

      server.close = async () => {
        closed = true;
        process.off('exit', cleanup);
        process.off('SIGINT', onSignal);
        process.off('SIGTERM', onSignal);
        cleanup();
        await closeServer();
      };

      process.once('exit', cleanup);
      process.once('SIGINT', onSignal);
      process.once('SIGTERM', onSignal);
    },
  };
};

export default hotFile;
