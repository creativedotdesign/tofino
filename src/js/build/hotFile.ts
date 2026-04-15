import type { Plugin, ResolvedConfig } from 'vite';
import fs from 'node:fs';
import path from 'node:path';

type HotFileOptions = {
  preferNetworkUrl?: boolean;
  publicUrl?: string;
};

/**
 * Vite plugin that writes the dev server URL to `{outDir}/hot` when the dev
 * server starts, and removes it on shutdown. WordPress reads this file to
 * detect the dev server without making an HTTP request.
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

      server.httpServer?.once('listening', () => {
        const localAddress = server.resolvedUrls?.local[0];
        const networkAddress = server.resolvedUrls?.network[0];
        const fallbackAddress = `http://localhost:${server.config.server.port}`;
        const explicitPublicUrl = options.publicUrl?.trim().replace(/\/+$/, '');

        const address =
          explicitPublicUrl ||
          (options.preferNetworkUrl ? networkAddress : localAddress) ||
          localAddress ||
          networkAddress ||
          fallbackAddress;

        fs.mkdirSync(path.dirname(hotPath), { recursive: true });
        fs.writeFileSync(hotPath, address);
      });

      const cleanup = (): void => {
        if (fs.existsSync(hotPath)) {
          fs.unlinkSync(hotPath);
        }
      };

      process.on('exit', cleanup);
      process.on('SIGINT', () => {
        cleanup();
        process.exit();
      });
      process.on('SIGTERM', () => {
        cleanup();
        process.exit();
      });
    },
  };
};

export default hotFile;
