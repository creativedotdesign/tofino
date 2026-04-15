import { bin, install, Tunnel } from 'cloudflared';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import type { Plugin, UserConfig } from 'vite';
import { bold, lightMagenta, yellow } from 'kolorist';

type CloudflareTunnelOptions = {
  enabled?: boolean;
  tunnelId?: string;
  tunnelName?: string;
  tunnelHostname?: string;
  tunnelPublicUrl?: string;
  siteOrigin?: string;
  vitePort?: number;
};

/**
 * Internal process-wide plugin state used to reuse tunnel instances.
 */
type GlobalState = {
  tunnel?: Tunnel;
  configPath?: string;
  configHash?: string;
};

const GLOBAL_KEY = Symbol.for('cloudflare-tunnel-plugin.state');

/**
 * Returns the shared cloudflare tunnel plugin state for this process.
 */
const getGlobalState = (): GlobalState => {
  const g = globalThis as Record<symbol, GlobalState>;
  g[GLOBAL_KEY] ??= {};
  return g[GLOBAL_KEY];
};

/**
 * Builds an ingress configuration file for cloudflared.
 *
 * @param tunnelId Tunnel ID used by cloudflared.
 * @param tunnelHostname Public tunnel hostname.
 * @param vitePort Local Vite dev server port.
 * @param siteOrigin Upstream site origin URL.
 * @param siteOriginHost Parsed host from site origin.
 * @returns YAML config content.
 */
function buildIngressYaml(
  tunnelId: string,
  tunnelHostname: string,
  vitePort: number,
  siteOrigin: string,
  siteOriginHost: string | undefined,
): string {
  const viteService = `http://localhost:${vitePort}`;
  const originRequestBlock = siteOriginHost
    ? `\n    originRequest:\n      httpHostHeader: ${siteOriginHost}`
    : '';

  return [
    `tunnel: ${tunnelId}`,
    `credentials-file: ${os.homedir()}/.cloudflared/${tunnelId}.json`,
    '',
    'ingress:',
    `  - hostname: ${tunnelHostname}`,
    `    path: /@vite-hmr`,
    `    service: ${viteService}`,
    `  - hostname: ${tunnelHostname}`,
    `    path: /(@vite|@id|@fs|src|node_modules|js|css)/.*`,
    `    service: ${viteService}`,
    `  - hostname: ${tunnelHostname}`,
    `    path: /sprite\\.svg`,
    `    service: ${viteService}`,
    `  - hostname: ${tunnelHostname}`,
    `    service: ${siteOrigin}${originRequestBlock}`,
    '  - service: http_status:404',
    '',
  ].join('\n');
}

/**
 * Safely parses a URL host value.
 *
 * @param url URL string to parse.
 * @returns Hostname with port when valid.
 */
function parseHost(url: string): string | undefined {
  try {
    return new URL(url).host;
  } catch {
    return undefined;
  }
}

/**
 * Installs the cloudflared binary if it is missing.
 */
async function ensureBinary(): Promise<void> {
  if (!fs.existsSync(bin)) {
    console.log('[cloudflare-tunnel] Installing cloudflared binary...');
    await install(bin);
  }
}

/**
 * Vite plugin that provisions and manages a Cloudflare tunnel in dev mode.
 *
 * @param options Plugin options sourced from environment/config.
 * @returns Vite plugin instance.
 */
const cloudflareTunnel = (options: CloudflareTunnelOptions = {}): Plugin => {
  const state = getGlobalState();

  const cleanup = (): void => {
    state.tunnel?.stop();
    state.tunnel = undefined;

    if (state.configPath && fs.existsSync(state.configPath)) {
      fs.unlinkSync(state.configPath);
      state.configPath = undefined;
    }
    state.configHash = undefined;
  };

  return {
    name: 'cloudflare-tunnel',
    apply: 'serve',

    config(): UserConfig | undefined {
      if (!options.enabled) return;

      const serverConfig: UserConfig['server'] = {};

      if (options.tunnelHostname) {
        serverConfig.allowedHosts = [options.tunnelHostname];
      }

      if (options.tunnelPublicUrl) {
        serverConfig.origin = options.tunnelPublicUrl;
      }

      if (options.tunnelHostname) {
        serverConfig.hmr = {
          protocol: 'wss',
          host: options.tunnelHostname,
          clientPort: 443,
          path: '/@vite-hmr',
        };
      }

      return { server: serverConfig };
    },

    async configureServer(server) {
      if (!options.enabled) return;

      const tunnelId = options.tunnelId?.trim();
      const tunnelName = options.tunnelName?.trim();
      const tunnelHostname = options.tunnelHostname?.trim();
      const siteOrigin = options.siteOrigin?.trim();

      if (!tunnelId || !tunnelName || !tunnelHostname || !siteOrigin) {
        server.config.logger.error(
          '[cloudflare-tunnel] Missing required env vars: VITE_TUNNEL_ID, VITE_TUNNEL_NAME, VITE_TUNNEL_HOSTNAME, VITE_ASSET_URL.',
        );
        return;
      }

      const vitePort = options.vitePort || Number(server.config.server.port) || 5173;
      const newHash = JSON.stringify({
        tunnelId,
        tunnelName,
        tunnelHostname,
        siteOrigin,
        vitePort,
      });

      // Reuse existing tunnel if config hasn't changed
      if (state.tunnel && state.configHash === newHash) {
        server.config.logger.info(
          `  ${lightMagenta('➜')}  ${bold('Cloudflare:')}${yellow(' reusing existing tunnel')}`,
        );
        return;
      }

      // Config changed — tear down old tunnel first
      if (state.tunnel) {
        cleanup();
      }

      await ensureBinary();

      server.httpServer?.once('listening', () => {
        state.configPath = path.join(os.tmpdir(), `tofino-cloudflared-${process.pid}.yml`);
        const configPath = state.configPath;
        fs.writeFileSync(
          configPath,
          buildIngressYaml(tunnelId, tunnelHostname, vitePort, siteOrigin, parseHost(siteOrigin)),
        );

        state.tunnel = new Tunnel(['tunnel', '--config', configPath, 'run', tunnelName]);
        state.configHash = newHash;

        state.tunnel.on('connected', (conn) => {
          server.config.logger.info(
            `  ${lightMagenta('➜')}  ${bold('Cloudflare:')}${yellow(` connected (${conn.location})`)}`,
          );
        });

        state.tunnel.on('error', (err) => {
          server.config.logger.error(`[cloudflare-tunnel] ${err.message}`);
        });

        state.tunnel.on('exit', (code) => {
          if (code !== 0) {
            server.config.logger.error(`[cloudflare-tunnel] exited with code ${code}`);
          }
        });
      });

      const closeServer = server.close.bind(server);
      server.close = async () => {
        cleanup();
        await closeServer();
      };

      process.once('SIGINT', cleanup);
      process.once('SIGTERM', cleanup);
    },
  };
};

export default cloudflareTunnel;
